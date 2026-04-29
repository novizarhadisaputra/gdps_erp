<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasDefaultRecord;

class WorkEquipment extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $table = 'work_equipment';

    protected $fillable = [
        'code',
        'name',
        'base_cost',
        'replacement_duration',
        'description',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'replacement_duration' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
