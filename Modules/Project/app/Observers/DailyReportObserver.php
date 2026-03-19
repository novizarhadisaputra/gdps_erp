<?php

namespace Modules\Project\Observers;

use App\Models\User;
use Filament\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Enums\DailyReportStatus;
use Modules\Project\Models\DailyReport;

class DailyReportObserver
{
    /**
     * Handle the DailyReport "updated" event.
     */
    public function updated(DailyReport $dailyReport): void
    {
        if ($dailyReport->isDirty('status')) {
            $this->notifyReporter($dailyReport);

            // If submitted, notify project managers/leads
            if ($dailyReport->status === DailyReportStatus::Submitted) {
                $this->notifyApprovers($dailyReport);
            }
        }
    }

    /**
     * Handle the DailyReport "created" event.
     */
    public function created(DailyReport $dailyReport): void
    {
        if ($dailyReport->status === DailyReportStatus::Submitted) {
            $this->notifyApprovers($dailyReport);
        }
    }

    protected function notifyReporter(DailyReport $report): void
    {
        $member = $report->reportedBy;
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

        $statusLabel = $report->status->getLabel();
        $color = match ($report->status) {
            DailyReportStatus::Approved => 'success',
            DailyReportStatus::Rejected => 'danger',
            default => 'info',
        };

        Notification::make()
            ->title("Daily Report {$statusLabel}")
            ->body("Your daily report for {$report->date->format('d M Y')} has been {$statusLabel}.")
            ->icon($report->status->getIcon())
            ->color($color)
            ->actions([
                NotificationAction::make('view')
                    ->button()
                    ->url("/admin/projects/{$report->project_id}/daily-reports/{$report->id}/discussions"),
            ])
            ->sendToDatabase($user);
    }

    protected function notifyApprovers(DailyReport $report): void
    {
        $project = $report->project;
        if (! $project) {
            return;
        }

        // Notify Project Manager (Oprep) and AMS
        $approvers = collect([$project->oprep, $project->ams])->filter();

        foreach ($approvers as $approver) {
            $user = User::where('email', $approver->email)->first();

            if ($user) {
                Notification::make()
                    ->title('Daily Report Submitted')
                    ->body("A new daily report has been submitted for project: {$project->name}")
                    ->icon(Heroicon::OutlinedDocumentMagnifyingGlass)
                    ->color('warning')
                    ->actions([
                        NotificationAction::make('view')
                            ->button()
                            ->url("/admin/projects/{$project->id}/daily-reports/{$report->id}/discussions"),
                    ])
                    ->sendToDatabase($user);
            }
        }
    }
}
