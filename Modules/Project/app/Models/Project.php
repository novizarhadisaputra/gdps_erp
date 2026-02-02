<?php

namespace Modules\Project\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\BillingOption;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\PaymentTerm;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Database\Factories\ProjectFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\Project\Database\Factories\ProjectFactory;

class Project extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'name',
        'status',
        'customer_id',
        'contract_id',
        'project_number',
        'work_scheme_id',
        'product_cluster_id',
        'tax_id',
        'payment_term_id',
        'project_type_id',
        'billing_option_id',
        'oprep_id',
        'ams_id',
        'project_area_id',
        'start_date',
        'end_date',
        'proposal_id',
        'profitability_analysis_id',
    ];

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    protected function casts(): array
    {
        return [];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('project_documents')
            ->useDisk('s3');

        $this->addMediaCollection('deliverables')
            ->useDisk('s3');
    }

    public function information(): HasOne
    {
        return $this->hasOne(ProjectInformation::class);
    }

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
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

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    public function billingOption(): BelongsTo
    {
        return $this->belongsTo(BillingOption::class);
    }

    public function oprep(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'oprep_id');
    }

    public function ams(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'ams_id');
    }

    public function getAmountAttribute(): float
    {
        return $this->proposal?->amount
            ?? $this->contract?->proposal?->amount
            ?? $this->profitabilityAnalysis?->revenue_per_month
            ?? 0.0;
    }
}
