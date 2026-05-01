<?php

namespace Modules\CRM\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CRM\Database\Factories\CustomerFactory;
use Modules\CRM\Observers\CustomerObserver;
use Modules\MasterData\Enums\ActiveStatus;
use Modules\MasterData\Enums\LegalEntityType;
use Modules\MasterData\Models\District;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\MasterData\Models\Village;
use Modules\Project\Models\Project;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(CustomerObserver::class)]
class Customer extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'code',
        'legal_entity_type',
        'name',
        'email',
        'phone',
        'address',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'contacts',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contacts' => 'array',
            'status' => ActiveStatus::class,
            'legal_entity_type' => LegalEntityType::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('npwp')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('legal_documents')
            ->useDisk('s3');

        $this->addMediaCollection('company_profile')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    public function accountMappings(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\Finance\Models\AccountMapping::class, 'mappable');
    }

    public function projectAreas(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Modules\MasterData\Models\ProjectArea::class, 'parentable');
    }
}
