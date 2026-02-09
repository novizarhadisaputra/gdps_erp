<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BpjsConfig extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'type',
        'category',
        'employer_rate',
        'employee_rate',
        'floor_type',
        'floor_nominal',
        'cap_type',
        'cap_nominal',
        'risk_level',
        'calculation_basis',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'employer_rate' => 'decimal:6',
            'employee_rate' => 'decimal:6',
            'floor_nominal' => 'decimal:2',
            'cap_nominal' => 'decimal:2',
            'calculation_basis' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
