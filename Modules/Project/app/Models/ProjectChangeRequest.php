<?php

namespace Modules\Project\Models;

use App\Traits\HasModuleSchema;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Enums\ProjectChangeRequestType;
use Modules\Project\Enums\TaskPriority;
use Modules\Project\Enums\TaskStatus;
use Modules\Project\Observers\ProjectChangeRequestObserver;

#[ObservedBy(ProjectChangeRequestObserver::class)]
class ProjectChangeRequest extends Model
{
    use HasFactory, HasModuleSchema, HasUuids, SoftDeletes;

    protected $fillable = [
        'project_id',
        'number',
        'sequence_number',
        'year',
        'type',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProjectChangeRequestType::class,
            'status' => ProjectChangeRequestStatus::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approve(): void
    {
        if ($this->status !== ProjectChangeRequestStatus::Submitted) {
            return;
        }

        $this->update(['status' => ProjectChangeRequestStatus::Approved]);

        ProjectTask::create([
            'project_id' => $this->project_id,
            'name' => 'Change Request: '.($this->type?->getLabel() ?? 'General'),
            'description' => $this->notes,
            'status' => TaskStatus::Todo,
            'priority' => TaskPriority::Medium,
        ]);
    }

    public function reject(?string $reason = null): void
    {
        if ($this->status !== ProjectChangeRequestStatus::Submitted) {
            return;
        }

        $this->update(['status' => ProjectChangeRequestStatus::Rejected]);
        // Optional: Logic to save rejection reason if a column exists
    }

    public function submit(): void
    {
        if ($this->status !== ProjectChangeRequestStatus::Draft) {
            return;
        }

        $this->update(['status' => ProjectChangeRequestStatus::Submitted]);
    }
}
