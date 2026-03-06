<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegencyMinimumWage extends Model
{
    use HasModuleSchema;
    use HasUuids;

    protected $fillable = [
        'project_area_id',
        'province',
        'type',
        'year',
        'amount',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => \Modules\MasterData\Enums\RegencyMinimumWageType::class,
            'amount' => 'decimal:2',
            'year' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }
}
