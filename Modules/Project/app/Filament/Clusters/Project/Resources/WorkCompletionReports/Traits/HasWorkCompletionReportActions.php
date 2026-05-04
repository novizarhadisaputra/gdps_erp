<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

trait HasWorkCompletionReportActions
{
    protected function getWorkCompletionReportHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->hidden(fn () => $this instanceof ViewRecord),
            EditAction::make()
                ->hidden(fn () => $this instanceof EditRecord),

            ActionGroup::make([
                $this->getExportPdfAction(),
                $this->getGenerateAccrueRevenueAction(),
                $this->getDiscussionsAction(),
            ])
                ->label('Options')
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->button(),

            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getApproveAction(),
                $this->getSendToCustomerAction(),
                $this->getResendEmailAction(),
                $this->getConfirmCustomerSignatureAction(),
                $this->getReviseAction(),
                $this->getRejectAction(),
            ])
                ->label('Workflow')
                ->icon(Heroicon::OutlinedChevronDown)
                ->color('primary')
                ->button(),
        ];
    }

    protected function getExportPdfAction(): Action
    {
        return Action::make('pdf')
            ->label('Export PDF')
            ->color('gray')
            ->icon('heroicon-o-arrow-down-tray')
            ->schema([
                Select::make('language')
                    ->label('Template Language')
                    ->options([
                        'id' => 'Indonesian (Bahasa Indonesia)',
                        'en' => 'English (International)',
                    ])
                    ->default('id')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                app()->setLocale($data['language']);

                $pdf = Pdf::loadView('project::pdf.work_completion_report', [
                    'record' => $record,
                    'language' => $data['language'],
                ]);

                $name = str_replace(['/', '\\'], '-', $record->number);
                $fileName = "{$name}.pdf";

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    $fileName
                );
            });
    }

    protected function getDiscussionsAction(): Action
    {
        return Action::make('discussions')
            ->label('Discussions')
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('info')
            ->url(fn (WorkCompletionReport $record) => "/admin/projects/{$record->project_id}/work-completion-reports/{$record->id}/discussions");
    }

    protected function getGenerateAccrueRevenueAction(): Action
    {
        return Action::make('generateAccrueRevenue')
            ->label('Generate Financial Documents')
            ->icon('heroicon-o-presentation-chart-bar')
            ->color('success')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Approved && ! $record->accrueRevenueItems()->exists())
            ->url(fn (WorkCompletionReport $record) => $this->getResource()::getUrl('generate-financial-documents', ['record' => $record]));
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit for Approval')
            ->color('info')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading('Submit BAPP for Approval')
            ->modalDescription('Are you sure you want to submit this Work Completion Report for internal approval? This will notify the first set of approvers.')
            ->action(function (WorkCompletionReport $record) {
                $record->update(['status' => WorkCompletionStatus::Submitted]);

                // Notify the first step approvers
                app(SignatureService::class)->notifyNextApprovers($record);

                Notification::make()->title('BAPP Submitted Successfully')->success()->send();
            });
    }

    protected function getSendToCustomerAction(): Action
    {
        return Action::make('send')
            ->label('Send to Customer')
            ->color('info')
            ->icon('heroicon-o-envelope')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Submitted)
            ->action(function (WorkCompletionReport $record) {
                if (! $record->hasMedia('draft_report')) {
                    Notification::make()
                        ->title('Missing Document')
                        ->body('Please upload Draft BAPP (Unsigned) before sending.')
                        ->warning()
                        ->send();

                    return;
                }

                $this->redirect(ClusterResource::getUrl('send', ['record' => $record]));
            });
    }

    protected function getResendEmailAction(): Action
    {
        return Action::make('resend')
            ->label('Resend Email')
            ->color('info')
            ->icon('heroicon-o-arrow-path')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Sent)
            ->action(function (WorkCompletionReport $record) {
                $this->redirect(ClusterResource::getUrl('send', ['record' => $record]));
            });
    }

    protected function getApproveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve & Sign')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->modalHeading('Authorize BAPP')
            ->modalDescription('Please enter your PIN to record your digital signature for this approval step.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve.'),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()
                        ->title('Invalid PIN')
                        ->danger()
                        ->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);
                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()
                        ->title('Access Denied')
                        ->body('You do not have the authority to approve this document.')
                        ->warning()
                        ->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()
                        ->title('Already Signed')
                        ->body('You have already signed this approval step.')
                        ->warning()
                        ->send();

                    return;
                }

                $recordedRole = null;
                if ($matchingRule->approver_type === 'Role') {
                    $userRoles = auth()->user()->roles;
                    $ruleRoleIdentifiers = $matchingRule->approver_role ?? [];
                    $matchedRole = $userRoles->first(fn ($role) => in_array($role->id, $ruleRoleIdentifiers) || in_array($role->name, $ruleRoleIdentifiers));
                    $recordedRole = $matchedRole?->name;
                }

                $record->addSignature(auth()->user(), 'Approver', $recordedRole);

                // If this was the last internal approval, we don't automatically move to Approved
                // because it still needs to be Sent to Customer and get their signature.
                // However, we should notify the next person in line.
                $service->notifyNextApprovers($record);
                $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                Notification::make()
                    ->title('BAPP Signed')
                    ->body('Your signature has been recorded.')
                    ->success()
                    ->send();
            })
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Submitted &&
                app(SignatureService::class)->getRequiredApprovers($record)->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) &&
                    app(SignatureService::class)->isEligibleApprover($rule, auth()->user())
                )
            );
    }

    protected function getConfirmCustomerSignatureAction(): Action
    {
        return Action::make('confirmCustomerSignature')
            ->label('Confirm Customer Signature')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Sent && $record->hasMedia('signed_report'))
            ->requiresConfirmation()
            ->modalHeading('Confirm BAPP Approval')
            ->modalDescription('By confirming this, you verify that the Signed BAPP (Final Scan) from the customer is valid. This will mark the BAPP as Approved.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your PIN to confirm customer signature receipt.'),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                if (! app(SignatureService::class)->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Invalid PIN')->danger()->send();

                    return;
                }

                $record->update(['status' => WorkCompletionStatus::Approved]);
                Notification::make()->title('BAPP Approved Successfully')->success()->send();
            });
    }

    protected function getReviseAction(): Action
    {
        return Action::make('revise')
            ->label('Revise / Return to Draft')
            ->color('warning')
            ->icon(Heroicon::OutlinedArrowPath)
            ->visible(fn (WorkCompletionReport $record) => in_array($record->status, [
                WorkCompletionStatus::Submitted,
                WorkCompletionStatus::Sent,
                WorkCompletionStatus::Approved,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Revise BAPP')
            ->modalDescription('This will move the BAPP back to Draft stage, allowing you to make changes. A revision snapshot will be created, and all existing signatures will be cleared.')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Revision')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $record->signatures()->delete();
                $record->update(['status' => WorkCompletionStatus::Draft]);

                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);

                Notification::make()
                    ->title('BAPP Returned to Draft')
                    ->body('A new revision has been created.')
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
            ->visible(fn (WorkCompletionReport $record) => in_array($record->status, [
                WorkCompletionStatus::Submitted,
                WorkCompletionStatus::Sent,
            ]))
            ->requiresConfirmation()
            ->modalHeading('Reject BAPP')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (WorkCompletionReport $record, array $data) {
                $record->update(['status' => WorkCompletionStatus::Rejected]);
                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);
                Notification::make()->title('BAPP Rejected')->danger()->send();
            });
    }
}
