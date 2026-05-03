<?php

namespace Modules\Finance\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ProfitabilityAnalysisRevision extends Model implements HasMedia
{
    use HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'profitability_analysis_id',
        'number',
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

    public function registerMediaCollections(): void
    {
        foreach (['tor', 'rfp', 'rfq', 'cogs_source', 'manpower_costing_backup', 'operational_costing_backup'] as $collection) {
            $this->addMediaCollection($collection)
                ->useDisk('s3');
        }
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
