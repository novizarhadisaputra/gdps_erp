<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AccountMapping extends Model
{
    use HasModuleSchema, HasUuids;

    protected $fillable = [
        'mappable_id',
        'mappable_type',
        'type',
        'revenue_type_id',
        'revenue_segment_id',
        'chart_of_account_id',
    ];

    public function mappable(): MorphTo
    {
        return $this->morphTo();
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    public function revenueType(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\RevenueType::class);
    }

    public function revenueSegment(): BelongsTo
    {
        return $this->belongsTo(\Modules\MasterData\Models\RevenueSegment::class);
    }
}
