<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPattern extends Model
{
    use HasDefaultRecord, HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'name',
        'days_per_week',
        'hours_per_day',
        'is_shift',
        'description',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'days_per_week' => 'integer',
            'hours_per_day' => 'decimal:2',
            'is_shift' => 'boolean',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }
}
