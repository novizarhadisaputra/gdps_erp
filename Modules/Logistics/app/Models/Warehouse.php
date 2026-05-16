<?php

namespace Modules\Logistics\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Logistics\Database\Factories\WarehouseFactory;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $table = 'warehouses';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'code',
        'address',
        'is_active',
    ];
}
