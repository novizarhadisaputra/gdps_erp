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
use Modules\MasterData\Models\ItemCategory;
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
                        $jobPositionId = $item['matched_id'] ?? null;

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

                    $existingItems = Item::where('is_active', true)->get(['id', 'name'])->toArray();
                    $existingCategories = ItemCategory::get(['id', 'name'])->toArray();
                    $existingUnits = \Modules\MasterData\Models\UnitOfMeasure::get(['id', 'name', 'code'])->toArray();

                    $context = [
                        'project_area' => $lead->projectArea?->name,
                        'default_duration_months' => $duration,
                        'existing_items' => $existingItems,
                        'existing_categories' => $existingCategories,
                        'existing_units' => $existingUnits,
                    ];

                    $materialCategory = ItemCategory::with('assetGroup')->where('name', 'ilike', '%Material%')->first() ?? ItemCategory::with('assetGroup')->first();
                    $equipmentCategory = ItemCategory::with('assetGroup')->where('name', 'ilike', '%Equipment%')->first() ?? $materialCategory;

                    // Fetch default UnitOfMeasure
                    $defaultUom = \Modules\MasterData\Models\UnitOfMeasure::where('name', 'ilike', 'pcs')
                        ->orWhere('name', 'ilike', 'pieces')
                        ->orWhere('name', 'ilike', 'ls')
                        ->first() ?? \Modules\MasterData\Models\UnitOfMeasure::first();

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
                        $itemId = $itemData['matched_id'] ?? null;
                        $basePrice = 0;

                        if (! $itemId || strtolower($itemId) === 'null') {
                            $existing = Item::where('name', 'ilike', $itemData['name'])->first();

                            if ($existing) {
                                $itemId = $existing->id;
                                $basePrice = (float) $existing->price;
                            } else {
                                $isAsset = ! empty($itemData['is_asset']) && $itemData['is_asset'];

                                // Map Item Category from AI or Fallback
                                $resolvedCategoryId = $itemData['matched_category_id'] ?? null;
                                $resolvedCategory = null;
                                if ($resolvedCategoryId) {
                                    $resolvedCategory = ItemCategory::with('assetGroup')->find($resolvedCategoryId);
                                }

                                if (! $resolvedCategory) {
                                    $resolvedCategory = $isAsset ? $equipmentCategory : $materialCategory;
                                    $resolvedCategoryId = $resolvedCategory?->id;
                                }

                                // Fallback logic for depreciation months
                                $deprMonthsInput = $itemData['depreciation_months'] ?? null;
                                if (! $deprMonthsInput) {
                                    $usefulLifeYears = $resolvedCategory?->assetGroup?->useful_life_years;
                                    if ($usefulLifeYears && $usefulLifeYears > 0) {
                                        $deprMonthsInput = $usefulLifeYears * 12;
                                    } else {
                                        $deprMonthsInput = $isAsset ? 48 : $duration;
                                    }
                                }

                                // Map Unit Of Measure from AI
                                $resolvedUnitId = $itemData['matched_unit_id'] ?? null;
                                if (! $resolvedUnitId) {
                                    $aiUnit = $itemData['unit'] ?? 'Pcs';
                                    $resolvedUom = \Modules\MasterData\Models\UnitOfMeasure::where('name', 'ilike', trim($aiUnit))
                                        ->orWhere('code', 'ilike', trim($aiUnit))
                                        ->first() ?? $defaultUom;
                                    $resolvedUnitId = $resolvedUom?->id;
                                }

                                $newItem = Item::create([
                                    'name' => $itemData['name'],
                                    'price' => (float) ($itemData['unit_price'] ?? 0),
                                    'item_category_id' => $resolvedCategoryId,
                                    'depreciation_months' => (int) round((float) $deprMonthsInput),
                                    'is_active' => true,
                                    'unit_id' => auth()->user()?->unit_id,
                                    'unit_of_measure_id' => $resolvedUnitId,
                                ]);
                                $itemId = $newItem->id;
                                $basePrice = (float) $newItem->price;
                            }
                        } else {
                            $existing = Item::with('category.assetGroup')->find($itemId);
                            if ($existing) {
                                $basePrice = (float) $existing->price;
                            }
                        }

                        $qty = (float) ($itemData['quantity'] ?? 1);
                        $excelPrice = (float) ($itemData['unit_price'] ?? 0);

                        // Handle price discrepancies and setup markup
                        $unitPrice = $basePrice > 0 ? $basePrice : $excelPrice;
                        $markupPercent = 0;
                        $priceAfterMarkup = $unitPrice;

                        if ($excelPrice > 0 && $excelPrice !== $unitPrice) {
                            $priceAfterMarkup = $excelPrice;
                            if ($unitPrice > 0) {
                                $markupPercent = (($priceAfterMarkup / $unitPrice) - 1) * 100;
                            } else {
                                $unitPrice = $excelPrice;
                            }
                        }

                        // Determine depreciation months for the costing item
                        $deprMonths = null;
                        if (! empty($itemData['depreciation_months'])) {
                            $deprMonths = (int) round((float) $itemData['depreciation_months']);
                        } else {
                            $isAsset = ! empty($itemData['is_asset']) && $itemData['is_asset'];
                            if (isset($existing) && $existing) {
                                $deprMonths = $existing->depreciation_months;
                                if (empty($deprMonths) || $deprMonths <= 0) {
                                    $usefulLifeYears = $existing->category?->assetGroup?->useful_life_years;
                                    if ($usefulLifeYears && $usefulLifeYears > 0) {
                                        $deprMonths = $usefulLifeYears * 12;
                                    }
                                }
                            } else {
                                $resolvedCategory = $isAsset ? $equipmentCategory : $materialCategory;
                                $usefulLifeYears = $resolvedCategory?->assetGroup?->useful_life_years;
                                if ($usefulLifeYears && $usefulLifeYears > 0) {
                                    $deprMonths = $usefulLifeYears * 12;
                                }
                            }

                            if (! $deprMonths) {
                                $deprMonths = $isAsset ? 48 : $duration;
                            }
                        }
                        $method = DepreciationMethod::StraightLine;

                        $total = $qty * $priceAfterMarkup;
                        $monthly = $total / ($deprMonths > 0 ? $deprMonths : 1);

                        CostingTemplateItem::create([
                            'costing_template_id' => $template->id,
                            'item_id' => $itemId, // Can be null if not found
                            'category' => $this->mapCategory($itemData['category'] ?? ''),
                            'name' => $itemData['name'],
                            'quantity' => $qty,
                            'unit' => $itemData['unit'] ?? 'Pcs',
                            'unit_price' => $unitPrice,
                            'markup_percent' => round($markupPercent, 2),
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
