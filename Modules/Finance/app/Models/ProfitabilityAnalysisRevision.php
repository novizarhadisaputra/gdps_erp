<?php

namespace Modules\Finance\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\CRM\Models\Comment;

class ProfitabilityAnalysisRevision extends Model
{
    use HasFactory, HasUuids, HasModuleSchema;

    protected $fillable = [
        'profitability_analysis_id',
        'revision_number',
        'snapshot',
        'reason',
        'user_id',
        'sequence_number',
        'year',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    public function profitabilityAnalysis(): BelongsTo
    {
        return $this->belongsTo(ProfitabilityAnalysis::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
