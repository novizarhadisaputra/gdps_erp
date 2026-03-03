<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Database\Factories\ProfitabilityAnalysisFactory;
use Modules\Finance\Enums\AssetOwnership;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Observers\ProfitabilityAnalysisObserver;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
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
        'work_scheme_id',
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
        'management_expense_rate',
        'interest_rate',
        'tax_rate',
        'ebitda',
        'ebit',
        'ebt',
        'net_profit',
        'net_profit_margin',
        'is_imported',
        'import_source_id',
        'payment_term_id',
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
            'management_expense_rate' => 'decimal:2',
            'interest_rate' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'analysis_details' => 'array',
            'project_number' => 'integer',
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

        $this->addMediaCollection('rfi')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('cogs_source')
            ->useDisk('s3')
            ->singleFile();
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
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

    public function workScheme(): BelongsTo
    {
        return $this->belongsTo(WorkScheme::class);
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
}
