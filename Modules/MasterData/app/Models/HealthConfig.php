<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HealthConfig extends Model
{
    use HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'employee_type',
        'employer_rate',
        'employee_rate',
        'floor_type',
        'cap_nominal',
        'employer_nominal',
        'employee_nominal',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
