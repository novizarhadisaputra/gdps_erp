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
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Traits\HasDigitalSignatures;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy([GeneralInformationObserver::class])]
class GeneralInformation extends Model implements HasMedia
{
    use HasDigitalSignatures, HasFactory, HasUuids, InteractsWithMedia;

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
        'signatures',
        'sequence_number',
        'year',
        'rr_submission_id',
        'rr_status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_start_date' => 'date',
            'estimated_end_date' => 'date',
            'risk_management' => 'array',
            'signatures' => 'array',
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
        return $this->belongsTo(\Modules\MasterData\Models\ProjectArea::class);
    }

    public function profitabilityAnalyses(): HasMany
    {
        return $this->hasMany(ProfitabilityAnalysis::class);
    }

    public function pics(): HasMany
    {
        return $this->hasMany(GeneralInformationPic::class);
    }
}
