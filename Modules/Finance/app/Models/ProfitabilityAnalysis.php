<?php

namespace Modules\Finance\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Database\Factories\ProfitabilityAnalysisFactory;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Observers\ProfitabilityAnalysisObserver;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\Project;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(ProfitabilityAnalysisObserver::class)]
class ProfitabilityAnalysis extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia, SoftDeletes;
    use HasModuleSchema;

    public ?string $revision_reason = null;

    protected static function newFactory()
    {
        return ProfitabilityAnalysisFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'number',
        'lead_id',
        'customer_id',
        'general_information_id',
        'proposal_id',
        'project_type_id',
        'product_cluster_id',
        'project_area_id',
        'revenue_per_month',
        'direct_cost',
        'depreciation',
        'management_fee',
        'management_fee_rate',
        'margin_percentage',
        'analysis_details',
        'project_number',
        'status',
        'asset_ownership',
        'interest_rate',
        'tax_rate',
        'manual_depreciation',
        'ebitda',
        'ebit',
        'ebt',
        'net_profit',
        'net_profit_margin',
        'is_imported',
        'import_source_id',
        'payment_term_id',
        'is_manual_cost',
        'work_scheme_id',
        'tax_id',
        'is_margin_approved',
        'revision_number',
        'previous_code',
        'start_date',
        'end_date',
        'revenue_segment_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProfitabilityAnalysisStatus::class,
            'asset_ownership' => AssetOwnership::class,
            'revenue_per_month' => 'decimal:2',
            'direct_cost' => 'decimal:2',
            'depreciation' => 'decimal:2',
            'management_fee' => 'decimal:2',
            'management_fee_rate' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
            'ebitda' => 'decimal:2',
            'ebit' => 'decimal:2',
            'ebt' => 'decimal:2',
            'net_profit' => 'decimal:2',
            'net_profit_margin' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'manual_depreciation' => 'decimal:2',
            'analysis_details' => 'array',
            'project_number' => 'integer',
            'is_manual_cost' => 'boolean',
            'is_margin_approved' => 'boolean',
            'revision_number' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'salary_increase_rate' => 'decimal:2',
            'project_duration' => 'decimal:2',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('tor')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('rfp')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('rfq')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('cogs_source')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('manpower_costing_backup')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('operational_costing_backup')
            ->useDisk('s3')
            ->singleFile();
    }

    public function proposal(): HasOne
    {
        return $this->hasOne(Proposal::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisRevision::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function generalInformation(): BelongsTo
    {
        return $this->belongsTo(GeneralInformation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function productCluster(): BelongsTo
    {
        return $this->belongsTo(ProductCluster::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
    }

    public function revenueSegment(): BelongsTo
    {
        return $this->belongsTo(RevenueSegment::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function monthlies(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisMonthly::class);
    }

    public function getDirectItems(): Collection
    {
        $topLevelItems = collect($this->analysis_details['manual_costs'] ?? []);

        return $topLevelItems->flatMap(function ($group) {
            $categoryId = $group['direct_cost_category_id'] ?? null;
            $category = $categoryId ? DirectCostCategory::find($categoryId) : null;
            $subItems = collect($group['sub_items'] ?? []);

            if ($subItems->isEmpty()) {
                return [
                    (object) [
                        'direct_cost_category_id' => $categoryId,
                        'category' => $category,
                        'total_monthly_cost' => self::parseNumericValue($group['amount'] ?? $group['total_monthly_cost'] ?? 0),
                        'unit_cost_price' => self::parseNumericValue($group['unit_amount'] ?? $group['unit_cost_price'] ?? 0),
                        'quantity' => self::parseNumericValue($group['quantity'] ?? 1),
                        'uom' => $group['uom'] ?? $group['unit_of_measure'] ?? 'Unit',
                        'is_manpower' => ($group['costable_type'] ?? null) === JobPosition::class || ($group['job_position_id'] ?? null) !== null,
                        'name' => $group['name'] ?? $group['job_position_name'] ?? $group['item_name'] ?? ($category?->name ?? 'Unnamed Item'),
                    ],
                ];
            }

            return $subItems->map(function ($item) use ($category, $categoryId) {
                $rawMonthlyCost = self::parseNumericValue($item['amount'] ?? $item['total_monthly_cost'] ?? 0);
                $duration = self::parseNumericValue($item['duration_months'] ?? $this->project_duration);
                $contribution = ($this->project_duration > 0) ? ($rawMonthlyCost * $duration / $this->project_duration) : $rawMonthlyCost;

                return (object) [
                    'direct_cost_category_id' => $categoryId,
                    'category' => $category,
                    'total_monthly_cost' => $rawMonthlyCost,
                    'duration_months' => $duration,
                    'monthly_contribution' => $contribution,
                    'unit_cost_price' => self::parseNumericValue($item['unit_amount'] ?? $item['unit_cost_price'] ?? 0),
                    'quantity' => self::parseNumericValue($item['quantity'] ?? 1),
                    'uom' => $item['uom'] ?? $item['unit_of_measure'] ?? (isset($item['job_position_name']) || isset($item['job_position_id']) ? 'Orang' : 'Unit'),
                    'is_manpower' => ($item['costable_type'] ?? null) === JobPosition::class || ($item['job_position_id'] ?? null) !== null,
                    'name' => $item['name'] ?? $item['job_position_name'] ?? $item['item_name'] ?? 'Unnamed Sub-Item',
                ];
            });
        });
    }

    public function getIndirectItems(): Collection
    {
        $jsonIndirect = collect($this->analysis_details['indirect_costs'] ?? []);

        return $jsonIndirect->map(function ($item) {
            return (object) [
                'direct_cost_category_id' => $item['direct_cost_category_id'] ?? null,
                'category' => isset($item['direct_cost_category_id']) ? DirectCostCategory::find($item['direct_cost_category_id']) : null,
                'total_monthly_cost' => self::parseNumericValue($item['total_monthly_cost'] ?? $item['amount'] ?? 0),
                'markup_percentage' => self::parseNumericValue($item['markup_percentage'] ?? 0),
                'calculation_type' => $item['calculation_type'] ?? 'fixed',
                'percentage_basis' => $item['percentage_basis'] ?? 'revenue',
                'unit_cost_price' => self::parseNumericValue($item['unit_cost_price'] ?? $item['unit_amount'] ?? 0),
                'costable_type' => null,
                'costable' => null,
            ];
        });
    }

    public function getTotalDirectCostByCategory(string $categoryCode): float
    {
        $items = $this->getDirectItems();

        return (float) $items->filter(function ($item) use ($categoryCode) {
            return ($item->category->code ?? null) === $categoryCode;
        })->sum('total_monthly_cost');
    }

    public function getTotalIndirectCost(): float
    {
        $revenue = (float) $this->revenue_per_month;
        $directCost = (float) $this->direct_cost;
        $items = $this->getIndirectItems();

        $total = 0;
        foreach ($items as $item) {
            $val = (float) ($item->total_monthly_cost ?? $item->unit_cost_price ?? 0);

            if (($item->calculation_type ?? 'fixed') === 'percentage') {
                $basis = $item->percentage_basis ?? 'revenue';
                $basisValue = $basis === 'revenue' ? $revenue : $directCost;
                $total += $basisValue * ($val / 100);
            } else {
                $total += $val;
            }
        }

        return $total;
    }

    public function isComplete(): bool
    {
        $hasItems = $this->getDirectItems()->isNotEmpty() || $this->getIndirectItems()->isNotEmpty();

        return ! empty($this->customer_id) &&
            ! empty($this->product_cluster_id) &&
            ! empty($this->work_scheme_id) &&
            ! empty($this->revenue_per_month) &&
            $this->margin_percentage !== null &&
            $hasItems;
    }

    public function isMarginApproved(): bool
    {
        // Check the database column first for performance and consistency
        if ($this->is_margin_approved) {
            return true;
        }

        return $this->isTypeApproved(ApprovalSignatureType::MarginApproval);
    }

    /**
     * Synchronize the boolean column with reality of signatures.
     */
    public function syncIsMarginApproved(): void
    {
        if ($this->getAttribute('is_margin_approved')) {
            return;
        }

        if ($this->isTypeApproved(ApprovalSignatureType::MarginApproval)) {
            $this->update(['is_margin_approved' => true]);
        }
    }

    public function getProjectDurationAttribute(): float
    {
        if ($this->start_date && $this->end_date) {
            $days = $this->start_date->diffInDays($this->end_date);

            return max(1, round($days / (365 / 12), 2));
        }

        if ($this->generalInformation && $this->generalInformation->estimated_start_date && $this->generalInformation->estimated_end_date) {
            $days = $this->generalInformation->estimated_start_date->diffInDays($this->generalInformation->estimated_end_date);

            return max(1, round($days / (365 / 12), 2));
        }

        return 1.0;
    }

    protected static function parseNumericValue(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (! is_string($value) || empty($value)) {
            return 0.0;
        }

        // Remove any non-numeric/separator characters (except minus sign)
        $value = preg_replace('/[^\d\.,-]/', '', $value);

        if (empty($value)) {
            return 0.0;
        }

        // Determine if it's Indonesian format (1.234,56) or US format (1,234.56)
        $dots = substr_count($value, '.');
        $commas = substr_count($value, ',');

        if ($dots > 0 && $commas > 0) {
            // Mixed separators - usually the last one is the decimal
            $lastDot = strrpos($value, '.');
            $lastComma = strrpos($value, ',');

            if ($lastDot > $lastComma) {
                // US Format: 1,234.56 -> remove commas
                $value = str_replace(',', '', $value);
            } else {
                // ID Format: 1.234,56 -> remove dots, replace comma with dot
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            }
        } elseif ($commas > 0 && $dots === 0) {
            // Only commas - could be 1,234 (US thousands) or 1234,56 (ID decimal)
            // In most ERP contexts here, a single comma in a string without dots is treated as decimal
            // unless it's clearly a thousand separator (e.g. 1,000).
            // However, to be safe for ID context:
            $value = str_replace(',', '.', $value);
        } elseif ($dots > 1) {
            // Multiple dots - must be thousand separators (ID: 1.000.000)
            $value = str_replace('.', '', $value);
        }

        return (float) $value;
    }

    public function getManpowerRequirementsAttribute(): array
    {
        $manpowerCategoryId = DirectCostCategory::where('code', 'manpower')->first()?->id;

        if ($this->is_manual_cost) {
            $items = collect($this->analysis_details['manual_costs'] ?? [])
                ->filter(fn ($item) => ($item['direct_cost_category_id'] ?? null) == $manpowerCategoryId)
                ->flatMap(fn ($item) => $item['sub_items'] ?? [$item]);
        } else {
            $manpowerId = $this->analysis_details['manpower_template_id'] ?? null;
            if (! $manpowerId) {
                return [];
            }
            $template = ManpowerTemplate::find($manpowerId);
            $items = collect($template?->getCostSimulation()['rows'] ?? []);
        }

        return $items->map(fn ($item) => [
            'job_position_id' => $item['job_position_id'] ?? $item['costable_id'] ?? null,
            'job_position_name' => $item['name'] ?? $item['job_position_name'] ?? 'Personnel',
            'quantity' => self::parseNumericValue($item['quantity'] ?? $item['qty'] ?? 1),
            'unit_cost' => self::parseNumericValue($item['unit_amount'] ?? $item['unit_cost'] ?? $item['unit_cost_price'] ?? 0),
            'total_monthly_cost' => self::parseNumericValue($item['amount'] ?? $item['line_total'] ?? $item['total_monthly_cost'] ?? 0),
            'risk_level' => $item['risk_level'] ?? 'very_low',
            'employee_type' => $item['employee_type'] ?? 'ppu',
            'is_labor_intensive' => $item['is_labor_intensive'] ?? false,
            'bill_thr_monthly' => $item['bill_thr_monthly'] ?? true,
            'bill_compensation_monthly' => $item['bill_compensation_monthly'] ?? true,
            'include_non_fixed_in_accruals' => $item['include_non_fixed_in_accruals'] ?? false,
            'extra_costs' => $item['extra_costs'] ?? [],
            'uom' => $item['uom'] ?? $item['unit_of_measure'] ?? 'Person',
            'ptkp_config_id' => $item['ptkp_config_id'] ?? null,
            'cost_breakdown' => $item['cost_breakdown'] ?? null,
        ])->toArray();
    }

    public function getFinancialAssumptionsAttribute(): array
    {
        $operationalCategoryId = DirectCostCategory::where('code', 'tools_equipment')->first()?->id;

        if ($this->is_manual_cost) {
            $opItems = collect($this->analysis_details['manual_costs'] ?? [])
                ->filter(fn ($item) => ($item['direct_cost_category_id'] ?? null) != DirectCostCategory::where('code', 'manpower')->first()?->id) // Filter out manpower, keep everything else as operational
                ->flatMap(fn ($item) => $item['sub_items'] ?? [$item]);
        } else {
            $costingId = $this->analysis_details['costing_template_id'] ?? null;
            if ($costingId) {
                $template = CostingTemplate::find($costingId);
                $opItems = $template?->costingTemplateItems ?? collect();
            } else {
                $opItems = collect();
            }
        }

        return [
            'interest_rate' => $this->interest_rate,
            'tax_rate' => $this->tax_rate,
            'management_fee_rate' => $this->management_fee_rate,
            'asset_ownership' => $this->asset_ownership,
            'is_manual_cost' => $this->is_manual_cost,
            'operational_costs' => $opItems->map(fn ($item) => [
                'item_id' => is_array($item) ? ($item['item_id'] ?? $item['costable_id'] ?? null) : $item->item_id,
                'item_name' => is_array($item) ? ($item['name'] ?? $item['item_name'] ?? null) : ($item->item?->name ?? $item->description),
                'quantity' => (float) (is_array($item) ? ($item['quantity'] ?? 1) : $item->quantity),
                'unit_cost' => (float) (is_array($item) ? ($item['unit_amount'] ?? $item['unit_cost_price'] ?? 0) : $item->unit_price),
                'total_monthly_cost' => (float) (is_array($item) ? ($item['amount'] ?? $item['total_monthly_cost'] ?? 0) : $item->monthly_cost),
                'uom' => is_array($item) ? ($item['uom'] ?? $item['unit_of_measure'] ?? 'Unit') : ($item->item?->unitOfMeasure?->name ?? 'Unit'),
                'calculation_type' => is_array($item) ? ($item['calculation_type'] ?? 'nominal') : 'nominal',
                'percentage_basis' => is_array($item) ? ($item['percentage_basis'] ?? null) : null,
            ])->toArray(),
        ];
    }
}
