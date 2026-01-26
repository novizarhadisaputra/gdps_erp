<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\ClientFactory;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class Client extends Model
{
    use HasFactory, HasUnitScoping;

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
        'status',
    ];

    protected static function newFactory(): \Modules\MasterData\Database\Factories\ClientFactory
    {
        return \Modules\MasterData\Database\Factories\ClientFactory::new();
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\Project\Models\Project::class);
    }
}
