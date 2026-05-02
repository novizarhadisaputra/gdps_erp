<?php

namespace Modules\Project\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCompletionReportRevision extends Model
{
    use HasFactory, HasModuleSchema, HasUuids;

    protected $fillable = [
        'work_completion_report_id',
        'revision_number',
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
}
