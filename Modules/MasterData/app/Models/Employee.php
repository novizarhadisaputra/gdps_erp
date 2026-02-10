<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\MasterData\Database\Factories\EmployeeFactory;
use Modules\MasterData\Observers\MasterDataObserver;
use Modules\MasterData\Traits\HasUnitScoping;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\MasterData\Database\Factories\EmployeeFactory;

#[ObservedBy([MasterDataObserver::class])]
class Employee extends Model implements HasMedia
{
    use HasFactory, HasUnitScoping, HasUuids, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'name',
        'email',
        'position',
        'department',
        'status',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('identity_card')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('resume')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('employment_contract')
            ->useDisk('s3')
            ->singleFile();
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
