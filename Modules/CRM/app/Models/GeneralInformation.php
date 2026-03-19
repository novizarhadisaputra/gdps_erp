<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Database\Factories\GeneralInformationFactory;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Observers\GeneralInformationObserver;
use Modules\Finance\Models\ProfitabilityAnalysis;
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
    use HasModuleSchema;

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
        'rr_document_path',
        'sequence_number',
        'year',
        'rr_submission_id',
        'rr_status',
        'rr_payload',
        'sales_plan_id',
        'work_scheme_id',
        'tax_id',
    ];

    protected function casts(): array
    {
        return [
            'estimated_start_date' => 'date',
            'estimated_end_date' => 'date',
            'status' => GeneralInformationStatus::class,
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

        // RFQ collection
        $this->addMediaCollection('rfq')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('other_documents');
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
     * Check if the Risk Register for this GI is approved.
     */
    public function hasRiskRegisterApproval(): bool
    {
        return $this->rr_status === 'approved';
    }

    /**
     * Determine if the document is fully approved.
     * Overridden to include Risk Register status check.
     */
    public function isFullyApproved(): bool
    {
        return $this->hasRiskRegisterApproval() && $this->traitIsFullyApproved();
    }

    public function getPicCustomerNameAttribute(): ?string
    {
        return $this->pics()->first()?->name;
    }

    public function getPicCustomerPhoneAttribute(): ?string
    {
        return $this->pics()->first()?->phone;
    }

    public function syncContactsToCustomer(): void
    {
        $customer = $this->customer;
        if (! $customer) {
            return;
        }

        $existingContacts = $customer->contacts ?? [];
        $newContacts = $this->pics->map(fn ($pic) => [
            'name' => $pic->name,
            'email' => $pic->email,
            'phone' => $pic->phone,
            'type' => (string) $pic->contact_role_id,
            'job_position' => null, // Not present in GI PICs
        ])->toArray();

        foreach ($newContacts as $newContact) {
            // Check if contact already exists by email or name
            $exists = false;
            foreach ($existingContacts as $existingContact) {
                if (! empty($newContact['email']) && ! empty($existingContact['email']) && strtolower($newContact['email']) === strtolower($existingContact['email'])) {
                    $exists = true;
                    break;
                }
                if (strtolower($newContact['name']) === strtolower($existingContact['name'])) {
                    $exists = true;
                    break;
                }
            }

            if (! $exists) {
                $existingContacts[] = $newContact;
            }
        }

        $customer->update(['contacts' => $existingContacts]);
    }

    public function toProfitabilityAnalysis(): ProfitabilityAnalysis
    {
        $pa = $this->lead->createProfitabilityAnalysis([
            'general_information_id' => $this->id,
            'customer_id' => $this->customer_id,
            'work_scheme_id' => $this->work_scheme_id,
            'product_cluster_id' => $this->salesPlan?->product_cluster_id ?? $this->lead?->product_cluster_id,
            'tax_id' => $this->tax_id,
            'project_area_id' => $this->project_area_id,
        ]);

        // Copy media collections to the new PA
        foreach (['tor', 'rfp', 'rfq'] as $collection) {
            $media = $this->getFirstMedia($collection);
            if ($media) {
                $media->copy($pa, $collection);
            }
        }

        return $pa;
    }

    public function isLocked(): bool
    {
        return in_array($this->status, [
            GeneralInformationStatus::Submitted,
            GeneralInformationStatus::Approved,
        ]);
    }

    public function isComplete(): bool
    {
        return ! empty($this->customer_id) &&
            ! empty($this->scope_of_work) &&
            ! empty($this->estimated_start_date) &&
            ! empty($this->estimated_end_date) &&
            ! empty($this->project_area_id) &&
            ! empty($this->work_scheme_id) &&
            ! empty($this->manpower_qualifications) &&
            ! empty($this->work_activities) &&
            ! empty($this->service_level) &&
            ! empty($this->billing_requirements) &&
            $this->pics()->exists();
    }
}
