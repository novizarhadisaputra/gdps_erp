<?php

namespace Modules\MasterData\Models;
 
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegencyMinimumWage extends Model
{
    use HasUuids;

    protected $fillable = [
        'project_area_id',
        'year',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'year' => 'integer',
        ];
    }

    public function projectArea(): BelongsTo
    {
        return $this->belongsTo(ProjectArea::class);
    }
}
