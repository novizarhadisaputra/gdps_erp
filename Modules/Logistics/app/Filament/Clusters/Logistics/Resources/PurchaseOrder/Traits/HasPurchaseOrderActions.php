<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Traits;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Logistics\Enums\PurchaseOrderStatus;
use Modules\Logistics\Models\PurchaseOrder;
use Modules\MasterData\Services\SignatureService;

trait HasPurchaseOrderActions
{
    protected function getPurchaseOrderHeaderActions(): array
    {
        return [
            EditAction::make()
                ->hidden(fn () => $this instanceof EditRecord)
                ->url(fn (PurchaseOrder $record) => static::getResource()::getUrl('edit', ['record' => $record])),

            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getApproveAction(),
                $this->getRejectAction(),
                $this->getMarkAsSentAction(),
                $this->getMarkAsCompletedAction(),

                DeleteAction::make()
                    ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrderStatus::Draft),
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
            ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrderStatus::Draft)
            ->requiresConfirmation()
            ->modalHeading('Submit Purchase Order')
            ->modalDescription('Are you sure you want to submit this PO for approval? This will notify the required approvers via email.')
            ->action(function (PurchaseOrder $record) {
                $record->update(['status' => PurchaseOrderStatus::Submitted]);

                app(SignatureService::class)->notifyNextApprovers($record);

                Notification::make()->title('Submitted Successfully')->success()->send();
            });
    }

    protected function getApproveAction(): Action
    {
        return Action::make('approve_order')
            ->label('Approve & Sign')
            ->color('success')
            ->icon(Heroicon::OutlinedCheckBadge)
            ->modalHeading('Approve Purchase Order')
            ->modalDescription('Please enter your PIN to record your digital signature for this approval step.')
            ->schema([
                TextInput::make('pin')
                    ->label('Signature PIN')
                    ->password()
                    ->required(),
            ])
            ->action(function (PurchaseOrder $record, array $data) {
                $service = app(SignatureService::class);

                if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                    Notification::make()->title('Invalid PIN')->danger()->send();

                    return;
                }

                $required = $service->getRequiredApprovers($record);
                $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user(), $record));

                if ($eligibleRules->isEmpty()) {
                    Notification::make()->title('Access Denied')->warning()->send();

                    return;
                }

                $matchingRule = $eligibleRules->first(fn ($rule) => ! $record->isRuleSatisfied($rule));

                if (! $matchingRule) {
                    Notification::make()->title('Already Signed')->warning()->send();

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

                if ($record->isFullyApproved()) {
                    $record->update(['status' => PurchaseOrderStatus::Approved]);
                }

                $service->notifyNextApprovers($record);
                $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                Notification::make()->title('Approved & Signed')->success()->send();
            })
            ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrderStatus::Submitted &&
                app(SignatureService::class)->getRequiredApprovers($record)->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) &&
                    app(SignatureService::class)->isEligibleApprover($rule, auth()->user(), $record)
                )
            );
    }

    protected function getRejectAction(): Action
    {
        return Action::make('reject_order')
            ->label('Reject')
            ->color('danger')
            ->icon(Heroicon::OutlinedXMark)
            ->visible(fn (PurchaseOrder $record) => $record->status === PurchaseOrderStatus::Submitted)
            ->requiresConfirmation()
            ->modalHeading('Reject Purchase Order')
            ->schema([
                TextInput::make('reason')
                    ->label('Reason for Rejection')
                    ->required(),
            ])
            ->action(function (PurchaseOrder $record, array $data) {
                $record->update(['status' => PurchaseOrderStatus::Rejected]);
                app(SignatureService::class)->notifyOwnerOnRejection($record, $data['reason']);
                Notification::make()->title('Rejected Successfully')->danger()->send();
            });
    }

    protected function getMarkAsSentAction(): Action
    {
        return Action::make('mark_as_sent')
            ->label('Mark as Sent')
            ->icon('heroicon-o-paper-airplane')
            ->color('warning')
            ->visible(fn ($record): bool => $record->status === PurchaseOrderStatus::Approved)
            ->requiresConfirmation()
            ->action(function ($record) {
                $record->update(['status' => PurchaseOrderStatus::Sent]);
                Notification::make()->title('PO marked as Sent to Vendor')->success()->send();
            });
    }

    protected function getMarkAsCompletedAction(): Action
    {
        return Action::make('mark_as_completed')
            ->label('Mark as Completed')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->visible(fn ($record): bool => $record->status === PurchaseOrderStatus::Sent)
            ->requiresConfirmation()
            ->action(function ($record) {
                $record->update(['status' => PurchaseOrderStatus::Completed]);
                Notification::make()->title('PO marked as Completed. Stock updated.')->success()->send();
            });
    }
}
