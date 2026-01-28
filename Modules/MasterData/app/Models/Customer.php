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

// use Modules\MasterData\Database\Factories\ClientFactory;

#[ObservedBy([MasterDataObserver::class])]
class Customer extends Model
{
    use HasFactory, HasUnitScoping, HasUuids;

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

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
