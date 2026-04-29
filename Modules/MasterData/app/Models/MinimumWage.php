<?php

namespace Modules\MasterData\Models;

use App\Traits\HasDefaultRecord;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinimumWage extends Model
{
    use HasDefaultRecord, HasModuleSchema, HasUuids;

    protected $fillable = [
        'project_area_id',
        'province',
        'type',
        'year',
        'amount',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'type' => \Modules\MasterData\Enums\MinimumWageType::class,
            'amount' => 'decimal:2',
            'year' => 'integer',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }
}
