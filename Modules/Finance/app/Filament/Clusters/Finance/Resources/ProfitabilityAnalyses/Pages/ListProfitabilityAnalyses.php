<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;

class ListProfitabilityAnalyses extends ListRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('importCogs')
                ->label('Create from COGS File')
                ->icon('heroicon-m-arrow-up-tray')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('cogs_file')
                        ->label('Excel COGS File')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->disk('s3')
                        ->directory('temp-imports'),
                ])
                ->action(function (array $data, \App\Services\AiProcessorService $aiService) {
                    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
                    if (! $disk->exists($data['cogs_file'])) {
                        return;
                    }

                    // 1. Download to temp
                    $tempPath = tempnam(sys_get_temp_dir(), 'import');
                    file_put_contents($tempPath, $disk->get($data['cogs_file']));

                    // 2. Extract raw data using Maatwebsite Excel
                    $rawData = \Maatwebsite\Excel\Facades\Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray
                    {
                        public function array(array $array) {}
                    }, $tempPath);

                    // 3. Process with AI
                    $structuredData = $aiService->processCogsData($rawData);

                    // 4. Create Profitability Analysis
                    $importId = \Illuminate\Support\Str::uuid();
                    $analysis = \Modules\Finance\Models\ProfitabilityAnalysis::create([
                        'is_imported' => true,
                        'import_source_id' => $importId,
                        'status' => 'draft',
                        // High-level manual fields remain empty or default for now
                    ]);

                    // 5. Attach file permanently
                    $analysis->addMedia($tempPath)
                        ->usingFileName(basename($data['cogs_file']))
                        ->toMediaCollection('cogs_source', 's3');

                    // 6. Map and Create Items
                    foreach ($structuredData['manpower'] ?? [] as $item) {
                        $analysis->items()->create([
                            'import_source_id' => $importId,
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_cost_price' => $item['basic_salary'] ?? 0,
                            'duration_months' => $item['duration_months'] ?? 1,
                            'costable_type' => \Modules\MasterData\Models\JobPosition::class,
                            'costable_id' => \Modules\MasterData\Models\JobPosition::where('name', 'like', '%'.$item['name'].'%')->first()?->id ?? null,
                        ]);
                    }

                    foreach ($structuredData['operational'] ?? [] as $item) {
                        $analysis->items()->create([
                            'import_source_id' => $importId,
                            'quantity' => $item['quantity'] ?? 1,
                            'unit_cost_price' => $item['unit_price'] ?? 0,
                            'duration_months' => $item['duration_months'] ?? 1,
                            'depreciation_months' => $item['depreciation_months'] ?? null,
                            'costable_type' => \Modules\MasterData\Models\Item::class,
                            'costable_id' => \Modules\MasterData\Models\Item::where('name', 'like', '%'.$item['name'].'%')->first()?->id ?? null,
                        ]);
                    }

                    if (file_exists($tempPath)) {
                        unlink($tempPath);
                    }
                    $disk->delete($data['cogs_file']);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Profitability Analysis created from COGS file')
                        ->send();

                    return redirect()->route('filament.admin.finance.resources.profitability-analyses.edit', $analysis);
                }),
            \Filament\Actions\CreateAction::make()
                ->label('Create Manual'),
        ];
    }
}
