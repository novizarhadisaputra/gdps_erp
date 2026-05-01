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
}
