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
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Database\Factories\ProfitabilityAnalysisFactory;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Observers\ProfitabilityAnalysisObserver;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Modules\Project\Models\Project;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(ProfitabilityAnalysisObserver::class)]
class ProfitabilityAnalysis extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;
    use HasModuleSchema;

    protected static function newFactory()
    {
        return ProfitabilityAnalysisFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_number',
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
        return $this->belongsTo(\Modules\MasterData\Models\ProjectType::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\WorkScheme::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisItem::class);
    }

    public function manpowerItems(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisItem::class)
            ->whereIn('costable_type', [
                JobPosition::class,
                ManpowerTemplate::class,
            ]);
    }

    public function operationalItems(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisItem::class)
            ->where('costable_type', Item::class);
    }

    public function indirectItems(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysisItem::class)
            ->whereNull('costable_type');
    }

    public function getDirectItems(): \Illuminate\Support\Collection
    {
        if ($this->is_manual_cost) {
            $items = collect($this->analysis_details['manual_costs'] ?? []);

            return $items->map(function ($item) {
                return (object) [
                    'direct_cost_category_id' => $item['direct_cost_category_id'] ?? null,
                    'category' => isset($item['direct_cost_category_id']) ? DirectCostCategory::find($item['direct_cost_category_id']) : null,
                    'total_monthly_cost' => self::parseNumericValue($item['amount'] ?? 0),
                    'unit_cost_price' => self::parseNumericValue($item['amount'] ?? 0),
                    'quantity' => self::parseNumericValue($item['quantity'] ?? 1),
                    'is_manpower' => false,
                    'costable_type' => null,
                    'costable' => null,
                ];
            });
        }

        return $this->items()
            ->whereHas('category', fn ($q) => $q->where('type', 'direct'))
            ->with('category')
            ->get();
    }

    public function getIndirectItems(): \Illuminate\Support\Collection
    {
        $jsonIndirect = collect($this->analysis_details['indirect_costs'] ?? []);

        if ($jsonIndirect->isNotEmpty()) {
            return $jsonIndirect->map(function ($item) {
                return (object) [
                    'direct_cost_category_id' => $item['direct_cost_category_id'] ?? null,
                    'category' => isset($item['direct_cost_category_id']) ? DirectCostCategory::find($item['direct_cost_category_id']) : null,
                    'total_monthly_cost' => self::parseNumericValue($item['total_monthly_cost'] ?? 0),
                    'markup_percentage' => self::parseNumericValue($item['markup_percentage'] ?? 0),
                    'costable_type' => null,
                    'costable' => null,
                    'unit_cost_price' => 0, // Added unit_cost_price with a default value
                ];
            });
        }

        return $this->items()
            ->whereHas('category', fn ($q) => $q->where('type', 'indirect'))
            ->with('category')
            ->get();
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
        return $this->isTypeApproved('MarginApproval');
    }

    protected static function parseNumericValue(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            // Remove thousand separators (.) and replace decimal separator (,) with (.)
            $cleanValue = str_replace(['.', ','], ['', '.'], $value);

            return (float) $cleanValue;
        }

        return 0.0;
    }
}
