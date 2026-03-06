<?php

namespace Modules\MasterData\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPositionNonFixedAllowance extends Model
{
    use HasModuleSchema;
    use HasUuids;

    protected $fillable = [
        'job_position_id',
        'non_fixed_allowance_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function jobPosition(): BelongsTo
    {
        return $this->belongsTo(JobPosition::class);
    }

    public function nonFixedAllowance(): BelongsTo
    {
        return $this->belongsTo(NonFixedAllowance::class);
    }
}
