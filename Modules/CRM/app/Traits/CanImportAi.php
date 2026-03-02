<?php

namespace Modules\CRM\Traits;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Jobs\ImportCostingAiJob;
use Modules\CRM\Jobs\ImportManpowerAiJob;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\JobPosition;

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
                    ->disk('local')
                    ->directory('temp-imports')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->required(),
            ])
            ->action(function (array $data) {
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    $lead = $this->getOwnerRecord();
                    $duration = 1;
                    if ($lead->start_date && $lead->end_date) {
                        $duration = $lead->start_date->diffInMonths($lead->end_date) ?: 1;
                    }

                    $jobPositions = JobPosition::where('is_active', true)->get(['id', 'name'])->toArray();
                    $context = [
                        'project_area' => $lead->projectArea?->name,
                        'default_duration_months' => $duration,
                        'existing_job_positions' => $jobPositions,
                        'lead_job_positions' => $lead->job_positions ?? [],
                    ];

                    $template = ManpowerTemplate::create([
                        'lead_id' => $lead->id,
                        'name' => '[AI Processing] '.($lead->customer?->name ?? $lead->title).' - '.now()->format('H:i'),
                        'description' => 'AI is currently processing your document and identifying manpower requirements...',
                        'project_area_id' => $lead->project_area_id,
                        'is_imported' => true,
                        'is_active' => true,
                    ]);

                    $template->addMedia($filePath)->toMediaCollection('source_file');
                    $persistentPath = $template->getFirstMedia('source_file')->getPath();

                    ImportManpowerAiJob::dispatch(
                        $persistentPath,
                        $context,
                        $template->id,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Import Started')
                        ->body("Record: '{$template->name}' has been created. AI is now populating items in the background.")
                        ->info()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }
            });
    }

    protected function getImportCostingAiAction(): Action
    {
        return Action::make('import_ai')
            ->label('Import via AI')
            ->icon('heroicon-o-sparkles')
            ->color('info')
            ->schema([
                FileUpload::make('file')
                    ->disk('local')
                    ->directory('temp-imports')
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                        'text/plain',
                        'image/jpeg',
                        'image/png',
                    ])
                    ->required(),
            ])
            ->action(function (array $data) {
                $filePath = Storage::disk('local')->path($data['file']);

                try {
                    $lead = $this->getOwnerRecord();
                    $duration = 1;
                    if ($lead->start_date && $lead->end_date) {
                        $duration = $lead->start_date->diffInMonths($lead->end_date) ?: 1;
                    }

                    $existingItems = Item::where('is_active', true)->get(['id', 'name'])->toArray();
                    $existingCategories = ItemCategory::get(['id', 'name'])->toArray();
                    $existingUnits = \Modules\MasterData\Models\UnitOfMeasure::get(['id', 'name', 'code'])->toArray();
                    $existingAssetGroups = \Modules\MasterData\Models\AssetGroup::get(['id', 'name', 'useful_life_years'])->toArray();

                    $context = [
                        'project_area' => $lead->projectArea?->name,
                        'default_duration_months' => $duration,
                        'existing_items' => $existingItems,
                        'existing_categories' => $existingCategories,
                        'existing_units' => $existingUnits,
                        'existing_asset_groups' => $existingAssetGroups,
                    ];

                    $template = CostingTemplate::create([
                        'lead_id' => $lead->id,
                        'name' => '[AI Processing] '.($lead->customer?->name ?? $lead->title).' - '.now()->format('H:i'),
                        'description' => 'AI is currently processing your document and identifying costing requirements...',
                        'pic_id' => auth()->id(),
                    ]);

                    $template->addMedia($filePath)->toMediaCollection('source_file');
                    $persistentPath = $template->getFirstMedia('source_file')->getPath();

                    ImportCostingAiJob::dispatch(
                        $persistentPath,
                        $context,
                        $template->id,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Import Started')
                        ->body("Record: '{$template->name}' has been created. AI is now populating items in the background.")
                        ->info()
                        ->send();

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Import Error')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
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
