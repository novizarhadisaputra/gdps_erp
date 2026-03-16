<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Database\Factories\ProductClusterFactory;

// use Modules\MasterData\Database\Factories\ProductClusterFactory;

class ProductCluster extends Model
{
    use HasFactory, HasUuids;
    use HasModuleSchema;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
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

    protected static function newFactory(): ProductClusterFactory
    {
        return ProductClusterFactory::new();
    }
}
