<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BpjsJpConfig extends Model
{
    use HasDefaultRecord, HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'employee_type',
        'employer_rate',
        'employee_rate',
        'cap_nominal',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
