<?php

namespace Modules\CRM\Jobs;

use App\Models\User;
use App\Services\AiProcessorService;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Enums\DepreciationMethod;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\CostingTemplateItem;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\UnitOfMeasure;

class ImportCostingAiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

    public $tries = 3;

    public function __construct(
        public string $filePath,
        public array $context,
        public string $templateId,
        public string $userId
    ) {}

    public function handle(AiProcessorService $aiService): void
    {
        ini_set('memory_limit', '2048M');

        $user = User::find($this->userId);
        $template = CostingTemplate::find($this->templateId);

        if (! $template || ! $user) {
            return;
        }

        $lead = $template->lead;

        Log::info("ImportCostingAiJob: Handling job for template: {$this->templateId}");

        // Resolve Path via Media Library
        $media = $template->getFirstMedia('source_file');
        if (! $media) {
            Log::error("ImportCostingAiJob: No media found for template: {$this->templateId}");

            return;
        }

        $localPath = null;
        try {
            if ($media->disk === 'local' || $media->disk === 'public') {
                $this->filePath = $media->getPath();
            } else {
                // Remote disk (S3, etc.), download to temporary local file
                $tempPath = tempnam(sys_get_temp_dir(), 'ai_import_');
                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                $this->filePath = $tempPath.($extension ? '.'.$extension : '');

                Log::info("ImportCostingAiJob: Downloading remote file from disk: {$media->disk} to {$this->filePath}");
                file_put_contents($this->filePath, Storage::disk($media->disk)->get($media->getPath()));
                $localPath = $this->filePath; // Mark for cleanup
            }

            Log::info("ImportCostingAiJob: Final resolved path: {$this->filePath}");

            if (! file_exists($this->filePath)) {
                throw new \Exception("File not found at resolved path: {$this->filePath}");
            }

            $duration = $this->context['default_duration_months'] ?? 1;

            // Re-fetch default categories and uom within the job to ensure they exist
            $materialCategory = ItemCategory::with('assetGroup')->where('name', 'ilike', '%Material%')->first() ?? ItemCategory::with('assetGroup')->first();
            $equipmentCategory = ItemCategory::with('assetGroup')->where('name', 'ilike', '%Equipment%')->first() ?? $materialCategory;

            $defaultUom = UnitOfMeasure::where('name', 'ilike', 'pcs')
                ->orWhere('name', 'ilike', 'pieces')
                ->orWhere('name', 'ilike', 'ls')
                ->first() ?? UnitOfMeasure::first();

            Log::info('ImportCostingAiJob: Calling AiProcessorService...');
            $processedData = $aiService->processCogsData($this->filePath, $this->context, 'items');
            $operationalData = collect($processedData['operational'] ?? [])
                ->filter(fn ($item) => $this->mapCategory($item['category'] ?? '') !== CostingCategory::Manpower)
                ->toArray();

            Log::info('ImportCostingAiJob: AI returned '.count($operationalData).' operational items.');

            if (empty($operationalData)) {
                Notification::make()
                    ->title('Costing Import Failed')
                    ->body('AI could not identify any operational data in the file.')
                    ->danger()
                    ->sendToDatabase($user);

                return;
            }

            $latestGi = $lead?->generalInformations()?->latest()?->first();

            $template->update([
                'name' => ($lead?->customer?->name ?? $lead?->title ?? 'Template').' - AI Auto Generated ('.now()->format('H:i').')',
                'description' => $latestGi?->scope_of_work ?? $template->description,
                'pic_id' => $user->id,
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
                            $resolvedUom = UnitOfMeasure::where('name', 'ilike', trim($aiUnit))
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
                            'unit_id' => $user->unit_id,
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
                    'item_id' => $itemId,
                    'category' => $this->mapCategory($itemData['category'] ?? ''),
                    'name' => $itemData['name'],
                    'quantity' => $qty,
                    'unit' => $itemData['unit'] ?? 'Pcs',
                    'unit_price' => $unitPrice,
                    'markup_percent' => round($markupPercent, 2),
                    'unit_price_markup' => $priceAfterMarkup,
                    'total_price' => $total,
                    'depreciation_months' => (int) round((float) $deprMonths),
                    'depreciation_method' => $method,
                    'monthly_cost' => $monthly,
                ]);
            }

            Log::info('ImportCostingAiJob: Successfully processed all items.');
            $template->refreshTotals();

            Notification::make()
                ->title('Costing Import Successful')
                ->body('AI has successfully processed and imported costing data for lead: '.($lead?->title ?? 'N/A'))
                ->success()
                ->sendToDatabase($user);

        } catch (\Exception $e) {
            Notification::make()
                ->title('Costing Import Error')
                ->body($e->getMessage())
                ->danger()
                ->sendToDatabase($user);
        } finally {
            if ($localPath && file_exists($localPath)) {
                Log::info("ImportCostingAiJob: Cleaning up temporary local file: {$localPath}");
                unlink($localPath);
            }
        }
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
