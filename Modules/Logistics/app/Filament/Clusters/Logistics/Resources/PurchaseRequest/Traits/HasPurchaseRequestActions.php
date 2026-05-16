<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Logistics\Enums\PurchaseRequestStatus;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\MasterData\Services\SignatureService;

trait HasPurchaseRequestActions
{
    protected function getPurchaseRequestHeaderActions(): array
    {
        return [
            EditAction::make()
                ->hidden(fn () => $this instanceof EditRecord)
                ->url(fn (PurchaseRequest $record) => static::getResource()::getUrl('edit', ['record' => $record])),

            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getApproveAction(),
                $this->getRejectAction(),

                DeleteAction::make()
                    ->visible(fn (PurchaseRequest $record) => $record->status === PurchaseRequestStatus::Draft),
            ])
                ->color('primary')
                ->button()
                ->visible(fn () => $this instanceof ViewRecord),
        ];
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit_for_approval')
            ->label('Submit for Approval')
            ->color('info')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->visible(fn (PurchaseRequest $record) => $record->status === PurchaseRequestStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading('Submit Purchase Request')
            ->modalDescription('Are you sure you want to submit this request for approval? This will notify the required approvers via email.')
            ->action(function (PurchaseRequest $record) {
                $record->update(['status' => PurchaseRequestStatus::Submitted]);

                app(SignatureService::class)->notifyNextApprovers($record);

                Notification::make()
                    ->title('Submitted Successfully')
                    ->body('Purchase request has been submitted for approval.')
                    ->success()
                    ->send();
            });
    }

    protected function getApproveAction(): Action
    {
        return Action::make('approve_request')
            ->label('Approve & Sign')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->modalHeading('Approve Purchase Request')
            ->modalDescription('Please enter your PIN to record your digital signature for this approval step.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required()
                    ->helperText('Enter your digital signature PIN to approve.'),
            ])
            ->action(function (PurchaseRequest $record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Invalid PIN')->danger()->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);
                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user(), $record));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()->title('Access Denied')->body('You do not have the authority to approve this document.')->warning()->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()->title('Already Signed')->body('You have already signed this approval step.')->warning()->send();

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

                // Check if fully satisfied
                if ($record->isFullyApproved()) {
                    $record->update(['status' => PurchaseRequestStatus::Approved]);
                }

                $service->notifyNextApprovers($record);
                $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                Notification::make()->title('Approved & Signed')->success()->send();
            })
            ->visible(fn (PurchaseRequest $record) => $record->status === PurchaseRequestStatus::Submitted &&
                app(SignatureService::class)->getRequiredApprovers($record)->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) &&
                    app(SignatureService::class)->isEligibleApprover($rule, auth()->user(), $record)
                )
            );
    }

    protected function getRejectAction(): Action
    {
        return Action::make('reject_request')
            ->label('Reject')
            ->color('danger')
            ->icon(Heroicon::OutlinedXMark)
            ->visible(fn (PurchaseRequest $record) => $record->status === PurchaseRequestStatus::Submitted)
            ->requiresConfirmation()
            ->modalHeading('Reject Purchase Request')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (PurchaseRequest $record, array $data) {
                $record->update(['status' => PurchaseRequestStatus::Rejected]);
                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);
                Notification::make()->title('Rejected Successfully')->danger()->send();
            });
    }
}
