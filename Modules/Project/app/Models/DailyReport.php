<?php

namespace Modules\Project\Models;

use App\Models\Comment;
use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Project\Enums\DailyReportStatus;
use Modules\Project\Observers\DailyReportObserver;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ObservedBy(DailyReportObserver::class)]
class DailyReport extends Model implements HasMedia
{
    use HasFactory, HasUuids;
    use HasModuleSchema, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'task_id',
        'reported_by_id',
        'date',
        'content',
        'weather',
        'site_condition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => DailyReportStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(ProjectMember::class, 'reported_by_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('site_photos')
            ->useDisk('public');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }
}
