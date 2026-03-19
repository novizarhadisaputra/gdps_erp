<?php

namespace Modules\Project\Observers;

use App\Models\User;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Models\ProjectTask;

class ProjectTaskObserver
{
    /**
     * Handle the ProjectTask "updated" event.
     */
    public function updated(ProjectTask $projectTask): void
    {
        if ($projectTask->isDirty('progress_percentage')) {
            $this->rollupProgress($projectTask);
        }

        if ($projectTask->isDirty('assigned_member_id') && $projectTask->assigned_member_id) {
            $this->notifyAssignedUser($projectTask);
        }
    }

    /**
     * Handle the ProjectTask "created" event.
     */
    public function created(ProjectTask $projectTask): void
    {
        // For new tasks, we also want to trigger rollup to reset parent/project averages
        $this->rollupProgress($projectTask);

        if ($projectTask->assigned_member_id) {
            $this->notifyAssignedUser($projectTask);
        }
    }

    /**
     * Handle the ProjectTask "deleted" event.
     */
    public function deleted(ProjectTask $projectTask): void
    {
        $this->rollupProgress($projectTask);
    }

    /**
     * Recursively rollup progress to parent tasks and the project.
     */
    protected function rollupProgress(ProjectTask $task): void
    {
        // 1. If has parent, recalculate parent progress
        if ($task->parent_id) {
            $parent = ProjectTask::find($task->parent_id);
            if ($parent) {
                // Average of children's progress_percentage
                $avgProgress = ProjectTask::where('parent_id', $parent->id)->avg('progress_percentage') ?? 0;
                $parent->update(['progress_percentage' => (int) $avgProgress]);
            }
        } else {
            // 2. If no parent (top level), recalculate project progress
            $project = $task->project;
            if ($project) {
                // Average of all top-level tasks for this project
                $avgProgress = ProjectTask::where('project_id', $project->id)
                    ->whereNull('parent_id')
                    ->avg('progress_percentage') ?? 0;

                $project->update(['progress_percentage' => (float) $avgProgress]);
            }
        }
    }

    protected function notifyAssignedUser(ProjectTask $task): void
    {
        $member = $task->assignedMember;
        if (! $member) {
            return;
        }

        $email = $member->memberable?->email;
        if (! $email) {
            return;
        }

        $user = User::where('email', $email)->first();
        if (! $user) {
            return;
        }

        Notification::make()
            ->title('New Task Assigned')
            ->body("You have been assigned to task: {$task->name}")
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->color('success')
            ->actions([
                NotificationAction::make('view')
                    ->button()
                    ->url("/admin/projects/{$task->project_id}/tasks/{$task->id}/discussions"),
            ])
            ->sendToDatabase($user);
    }
}
