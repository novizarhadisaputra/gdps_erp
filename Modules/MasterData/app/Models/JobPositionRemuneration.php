<?php

namespace Modules\MasterData\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPositionRemuneration extends Model
{
    use HasUuids;

    protected $fillable = [
        'job_position_id',
        'remuneration_component_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function remunerationComponent(): BelongsTo
    {
        return $this->belongsTo(RemunerationComponent::class);
    }
}
