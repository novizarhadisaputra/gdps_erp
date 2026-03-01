<?php

namespace Modules\CRM\Traits;

use App\Services\AiProcessorService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\CostingTemplateItem;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use PhpOffice\PhpSpreadsheet\IOFactory;

trait CanImportAi
{
    protected function getImportManpowerAiAction(): Action
    {
        return Action::make('import_ai')
            ->label('Import via AI')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->schema([
                FileUpload::make('file')
                    ->label('Excel File')
                    ->disk('local')
                    ->directory('temp-imports')
                    ->required(),
            ])
            ->action(function (array $data, AiProcessorService $aiService) {
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    $lead = $this->getOwnerRecord();
                    $duration = 1;
                    if ($lead->start_date && $lead->end_date) {
                        $duration = $lead->start_date->diffInMonths($lead->end_date) ?: 1;
                    }

                    $latestGi = $lead->generalInformations()->latest()->first();

                    $jobPositions = JobPosition::where('is_active', true)->get(['id', 'name'])->toArray();
                    $context = [
                        'project_area' => $lead->projectArea?->name,
                        'default_duration_months' => $duration,
                        'existing_job_positions' => $jobPositions,
                        'lead_job_positions' => $lead->job_positions ?? [],
                    ];

                    $processedData = $aiService->processCogsData($rows, $context);
                    $manpowerData = $processedData['manpower'] ?? [];

                    if (empty($manpowerData)) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('AI could not identify any manpower data in the file.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $template = ManpowerTemplate::create([
                        'lead_id' => $lead->id,
                        'name' => ($lead->customer?->name ?? $lead->title).' - AI Auto Generated ('.now()->format('Y-m-d H:i').')',
                        'description' => $latestGi?->scope_of_work,
                        'project_area_id' => $latestGi?->project_area_id ?? $lead->project_area_id,
                        'is_imported' => true,
                        'is_active' => true,
                    ]);

                    foreach ($manpowerData as $item) {
                        $jobPositionId = $item['matched_id'];

                        if (! $jobPositionId || strtolower($jobPositionId) === 'null') {
                            $existing = JobPosition::where('name', 'ilike', $item['name'])->first();
                            if ($existing) {
                                $jobPositionId = $existing->id;
                            } else {
                                $newJob = JobPosition::create([
                                    'name' => $item['name'],
                                    'is_active' => true,
                                    'is_labor_intensive' => true,
                                    'risk_level' => 'Low',
                                ]);
                                $jobPositionId = $newJob->id;
                            }
                        }

                        ManpowerTemplateItem::create([
                            'manpower_template_id' => $template->id,
                            'job_position_id' => $jobPositionId,
                            'quantity' => $item['quantity'] ?? 1,
                            'basic_salary' => $item['basic_salary'] ?? 0,
                            'notes' => $item['notes'] ?? '',
                        ]);
                    }

                    Notification::make()
                        ->title('Import Successful')
                        ->body('AI has successfully processed and imported the manpower data.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($data['file']);
                }
            });
    }

    protected function getImportCostingAiAction(): Action
    {
        return Action::make('import_ai')
            ->label('Import via AI')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->form([
                FileUpload::make('file')
                    ->label('Excel File')
                    ->disk('local')
                    ->directory('temp-imports')
                    ->required(),
            ])
            ->action(function (array $data, AiProcessorService $aiService) {
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    $spreadsheet = IOFactory::load($filePath);
                    $worksheet = $spreadsheet->getActiveSheet();
                    $rows = $worksheet->toArray();

                    $lead = $this->getOwnerRecord();
                    $duration = 1;
                    if ($lead->start_date && $lead->end_date) {
                        $duration = $lead->start_date->diffInMonths($lead->end_date) ?: 1;
                    }

                    $latestGi = $lead->generalInformations()->latest()->first();

                    $existingItems = Item::where('is_active', true)->get(['id', 'name'])->toArray();
                    $context = [
                        'project_area' => $lead->projectArea?->name,
                        'default_duration_months' => $duration,
                        'existing_items' => $existingItems,
                    ];

                    $processedData = $aiService->processCogsData($rows, $context);
                    $operationalData = collect($processedData['operational'] ?? [])
                        ->filter(fn ($item) => $this->mapCategory($item['category'] ?? '') !== CostingCategory::Manpower)
                        ->toArray();

                    if (empty($operationalData)) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body('AI could not identify any operational data in the file.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $template = CostingTemplate::create([
                        'lead_id' => $lead->id,
                        'name' => ($lead->customer?->name ?? $lead->title).' - AI Auto Generated ('.now()->format('Y-m-d H:i').')',
                        'description' => $latestGi?->scope_of_work,
                        'pic_id' => auth()->id(),
                    ]);

                    foreach ($operationalData as $itemData) {
                        $itemId = $itemData['matched_id'];

                        if (! $itemId || strtolower($itemId) === 'null') {
                            $existing = Item::where('name', 'ilike', $itemData['name'])->first();

                            if ($existing) {
                                $itemId = $existing->id;
                            } else {
                                $newItem = Item::create([
                                    'name' => $itemData['name'],
                                    'price' => (float) ($itemData['unit_price'] ?? 0),
                                    'depreciation_months' => (float) ($itemData['depreciation_months'] ?? ($itemData['is_asset'] ? 48 : $duration)),
                                    'is_active' => true,
                                    'unit_id' => auth()->user()?->unit_id,
                                ]);
                                $itemId = $newItem->id;
                            }
                        }

                        $qty = (float) ($itemData['quantity'] ?? 1);
                        $price = (float) ($itemData['unit_price'] ?? 0);
                        $markupPercent = 0;
                        $deprMonths = (float) ($itemData['depreciation_months'] ?? ($itemData['is_asset'] ? 48 : $duration));
                        $method = DepreciationMethod::StraightLine;

                        $priceAfterMarkup = $price * (1 + ($markupPercent / 100));
                        $total = $qty * $priceAfterMarkup;
                        $monthly = $total / ($deprMonths > 0 ? $deprMonths : 1);

                        CostingTemplateItem::create([
                            'costing_template_id' => $template->id,
                            'item_id' => $itemId, // Can be null if not found
                            'category' => $this->mapCategory($itemData['category'] ?? ''),
                            'name' => $itemData['name'],
                            'quantity' => $qty,
                            'unit' => $itemData['unit'] ?? 'Pcs',
                            'unit_price' => $price,
                            'markup_percent' => $markupPercent,
                            'unit_price_markup' => $priceAfterMarkup,
                            'total_price' => $total,
                            'depreciation_months' => $deprMonths,
                            'depreciation_method' => $method,
                            'monthly_cost' => $monthly,
                        ]);
                    }

                    Notification::make()
                        ->title('Import Successful')
                        ->body('AI has successfully processed and imported the costing data.')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                } finally {
                    Storage::disk('local')->delete($data['file']);
                }
            });
    }

    protected function mapCategory(string $aiCategory): CostingCategory
    {
        $categoryMap = [
            'tools' => CostingCategory::ToolsEquipment,
            'equipment' => CostingCategory::ToolsEquipment,
            'material' => CostingCategory::MaterialConsumables,
            'consumables' => CostingCategory::MaterialConsumables,
            'it' => CostingCategory::ItSystem,
            'system' => CostingCategory::ItSystem,
            'software' => CostingCategory::ItSystem,
            'vehicle' => CostingCategory::Vehicle,
            'transport' => CostingCategory::Vehicle,
            'infrastructure' => CostingCategory::Infrastructure,
            'manpower' => CostingCategory::Manpower,
        ];

        $aiCategory = strtolower($aiCategory);

        foreach ($categoryMap as $key => $category) {
            if (str_contains($aiCategory, $key)) {
                return $category;
            }
        }

        return CostingCategory::Other;
    }
}
