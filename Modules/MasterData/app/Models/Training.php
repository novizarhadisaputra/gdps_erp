<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\MasterData\Traits\HasDefaultRecord;

class Training extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'code',
        'name',
        'base_cost',
        'validity_period',
        'description',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'validity_period' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
