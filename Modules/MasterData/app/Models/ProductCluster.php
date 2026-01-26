<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasUnitScoping;

// use Modules\MasterData\Database\Factories\ProductClusterFactory;

#[ObservedBy([\Modules\MasterData\Observers\MasterDataObserver::class])]
class ProductCluster extends Model
{
    use HasFactory, HasUnitScoping;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'unit_id',
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function newFactory(): \Modules\MasterData\Database\Factories\ProductClusterFactory
    {
        return \Modules\MasterData\Database\Factories\ProductClusterFactory::new();
    }
}
