<?php

namespace Modules\Project\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkCompletionReportRevision extends Model implements HasMedia
{
    use HasFactory, HasModuleSchema, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'work_completion_report_id',
        'number',
        'sequence_number',
        'year',
        'snapshot',
        'reason',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function workCompletionReport(): BelongsTo
    {
        return $this->belongsTo(WorkCompletionReport::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('draft_report')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('signed_report')
            ->useDisk('s3')
            ->singleFile();

        $this->addMediaCollection('completion_documents')
            ->useDisk('s3')
            ->singleFile();
    }
}
