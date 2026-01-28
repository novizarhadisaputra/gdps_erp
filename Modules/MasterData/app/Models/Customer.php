<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\MasterData\Database\Factories\CustomerFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;
use Modules\Project\Models\Project;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\MasterData\Database\Factories\ClientFactory;

#[ObservedBy([MasterDataObserver::class])]
class Customer extends Model implements HasMedia
{
    use HasFactory, HasUnitScoping, HasUuids, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'legal_entity_type',
        'name',
        'email',
        'phone',
        'address',
        'contacts',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'contacts' => 'array',
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
}
