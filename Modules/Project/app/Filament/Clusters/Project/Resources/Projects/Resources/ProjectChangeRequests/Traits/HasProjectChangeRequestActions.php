<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Traits;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Models\ProjectChangeRequest;

trait HasProjectChangeRequestActions
{
    protected function getProjectChangeRequestHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (ProjectChangeRequest $record) => $record->status === ProjectChangeRequestStatus::Draft),

            $this->getSubmitAction(),
            $this->getApproveAction(),
            $this->getRejectAction(),
        ];
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit Request')
            ->color('info')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (ProjectChangeRequest $record) => $record->status === ProjectChangeRequestStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading('Submit Change Request')
            ->modalDescription('Are you sure you want to submit this change request for approval?')
            ->action(function (ProjectChangeRequest $record) {
                $record->submit();

                Notification::make()
                    ->title('Request Submitted')
                    ->body('The project change request has been submitted for approval.')
                    ->success()
                    ->send();
            });
    }

    protected function getApproveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->visible(fn (ProjectChangeRequest $record) => $record->status === ProjectChangeRequestStatus::Submitted)
            ->requiresConfirmation()
            ->modalHeading('Approve Change Request')
            ->modalDescription('By approving this, the changes will be officially acknowledged.')
            ->action(function (ProjectChangeRequest $record) {
                $record->approve();

                Notification::make()
                    ->title('Request Approved & Task Created')
                    ->body('The change request has been approved and a new task has been added to the project.')
                    ->success()
                    ->send();
            });
    }

    protected function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->color('danger')
            ->icon(Heroicon::OutlinedXMark)
            ->visible(fn (ProjectChangeRequest $record) => $record->status === ProjectChangeRequestStatus::Submitted)
            ->requiresConfirmation()
            ->modalHeading('Reject Change Request')
            ->modalDescription('Please provide a reason for rejecting this request.')
            ->form([
                \Filament\Forms\Components\Textarea::make('reason')
                    ->label('Rejection Reason')
                    ->required(),
            ])
            ->action(function (ProjectChangeRequest $record, array $data) {
                $record->reject($data['reason'] ?? null);

                Notification::make()
                    ->title('Request Rejected')
                    ->danger()
                    ->send();
            });
    }
}
