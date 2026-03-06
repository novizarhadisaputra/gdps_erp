<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxRateTer extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'category',
        'min_gross',
        'max_gross',
        'rate',
        'is_active',
    ];

    protected $casts = [
        'min_gross' => 'decimal:2',
        'max_gross' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
