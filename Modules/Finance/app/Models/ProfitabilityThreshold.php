<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitabilityThreshold extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'min_gpm',
        'min_npm',
        'description',
    ];

    protected $casts = [
        'min_gpm' => 'decimal:2',
        'min_npm' => 'decimal:2',
    ];
}
