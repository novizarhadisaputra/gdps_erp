<?php

namespace Modules\Project\Observers;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Models\ProjectChangeRequest;

class ProjectChangeRequestObserver
{
    /**
     * Handle the ProjectChangeRequest "creating" event.
     */
    public function creating(ProjectChangeRequest $request): void
    {
        if (empty($request->number)) {
            $year = date('Y');
            $shortYear = date('y');

            $latest = ProjectChangeRequest::withTrashed()
                ->where('year', $year)
                ->orderBy('sequence_number', 'desc')
                ->first();

            $sequence = $latest ? (int) $latest->sequence_number + 1 : 1;

            $request->year = (int) $year;
            $request->sequence_number = $sequence;
            $request->number = sprintf('GDPS/UB/PCR-%03d/%s', $sequence, $shortYear);
        }
    }

    /**
     * Handle the ProjectChangeRequest "updated" event.
     */
    public function updated(ProjectChangeRequest $request): void
    {
        if ($request->wasChanged('status') && $request->status === ProjectChangeRequestStatus::Submitted) {
            $this->notifyApprovers($request);
        }
    }

    protected function notifyApprovers(ProjectChangeRequest $request): void
    {
        $project = $request->project;
        if (! $project) {
            return;
        }

        // Notify Project Manager (Oprep) and AMS
        $approvers = collect([$project->oprep, $project->ams])->filter();

        foreach ($approvers as $approver) {
            $user = User::where('email', $approver->email)->first();

            if ($user) {
                Notification::make()
                    ->title('New Project Change Request Submitted')
                    ->body("A new PCR ({$request->number}) has been submitted for project: {$project->name}")
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('warning')
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url("/admin/project-change-requests/{$request->id}"),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }
}
