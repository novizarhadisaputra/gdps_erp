<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Observers\GeneralInformationObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\CRM\Database\Factories\GeneralInformationFactory;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([GeneralInformationObserver::class])]
class GeneralInformation extends Model implements HasMedia
{
    use HasDigitalSignatures {
        isFullyApproved as traitIsFullyApproved;
    }
    use HasFactory, HasUuids, InteractsWithMedia;

    protected static function newFactory(): GeneralInformationFactory
    {
        return GeneralInformationFactory::new();
    }

    protected $table = 'general_informations';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_number',
        'lead_id',
        'customer_id',
        'status',
        'scope_of_work',
        'location',
        'project_area_id',
        'estimated_start_date',
        'estimated_end_date',
        'manpower_qualifications',
        'work_activities',
        'service_level',
        'billing_requirements',
        'risk_management',
        'description',
        'remarks',
        'rr_document_number',
        'sequence_number',
        'year',
        'rr_submission_id',
        'rr_status',
        'rr_payload',
        'sales_plan_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_start_date' => 'date',
            'estimated_end_date' => 'date',
            'risk_management' => 'array',
            'rr_payload' => 'array',
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

        // RFI collection is already here
        $this->addMediaCollection('rfi')
            ->useDisk('s3')
            ->singleFile();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }

    public function profitabilityAnalyses(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysis::class);
    }

    public function pics(): HasMany
    {
        return $this->hasMany(GeneralInformationPic::class);
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

    public function salesPlan(): BelongsTo
    {
        return $this->belongsTo(SalesPlan::class);
    }

    /**
     * Determine if the document is fully approved.
     * Overridden to include Risk Register status check.
     */
    public function isFullyApproved(): bool
    {
        return $this->rr_status === 'approved' && $this->traitIsFullyApproved();
    }

    public function getPicCustomerNameAttribute(): ?string
    {
        return $this->pics()->first()?->name;
    }

    public function getPicCustomerPhoneAttribute(): ?string
    {
        return $this->pics()->first()?->phone;
    }

    public function toProfitabilityAnalysis(): ProfitabilityAnalysis
    {
        $lead = $this->lead;

        $pa = $lead->profitabilityAnalyses()->create([
            'customer_id' => $lead->customer_id,
            'general_information_id' => $this->id,
            'work_scheme_id' => $lead->work_scheme_id,
            'project_area_id' => $this->project_area_id,
            'product_cluster_id' => $lead->product_cluster_id,
            'status' => 'draft',
        ]);

        // Copy media collections to the new PA
        foreach (['tor', 'rfp', 'rfi'] as $collection) {
            $media = $this->getFirstMedia($collection);
            if ($media) {
                $media->copy($pa, $collection);
            }
        }

        return $pa;
    }
}
