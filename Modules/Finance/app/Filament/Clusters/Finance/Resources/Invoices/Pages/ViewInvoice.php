<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Traits\HasInvoiceActions;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Services\SignatureService;

class ViewInvoice extends ViewRecord
{
    use HasInvoiceActions;

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submitInvoice')
                ->label('Submit')
                ->color('primary')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (Invoice $record) => $record->status === InvoiceStatus::Draft)
                ->requiresConfirmation()
                ->action(function (Invoice $record) {
                    $record->update(['status' => InvoiceStatus::Submitted]);

                    Notification::make()
                        ->title('Invoice Submitted')
                        ->body('The invoice has been submitted for approval.')
                        ->success()
                        ->send();
                }),

            Action::make('approveInvoice')
                ->label('Approve Invoice')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(function (Invoice $record) {
                    if ($record->status !== InvoiceStatus::Submitted) {
                        return false;
                    }

                    if ($record->isFullyApproved()) {
                        return false;
                    }

                    $service = app(SignatureService::class);
                    $required = $service->getRequiredApprovers($record)
                        ->where('signature_type', 'Approver');

                    return $required->contains(fn ($rule) => ! $record->isRuleSatisfied($rule) && $service->isEligibleApprover($rule, auth()->user()));
                })
                ->modalHeading('Approve Invoice')
                ->modalDescription('Please verify the invoice details. Entering your PIN will record your digital signature for this document.')
                ->modalSubmitActionLabel('Approve & Sign')
                ->schema([
                    TextInput::make('pin')
                        ->label('Signature PIN')
                        ->password()
                        ->required()
                        ->helperText('Enter your digital signature PIN to approve the Invoice.'),
                ])
                ->action(function (Invoice $record, array $data) {
                    $service = app(SignatureService::class);

                    if (! $service->verifyPin(auth()->user(), $data['pin'])) {
                        Notification::make()
                            ->title('Invalid PIN')
                            ->danger()
                            ->send();

                        return;
                    }

                    $required = $service->getRequiredApprovers($record)
                        ->where('signature_type', 'Approver');

                    $eligibleRules = $required->filter(fn ($rule) => $service->isEligibleApprover($rule, auth()->user()));

                    if ($eligibleRules->isEmpty()) {
                        Notification::make()
                            ->title('Access Denied')
                            ->body('You do not have the authority to approve this Invoice.')
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

                    $service->notifyNextApprovers($record);
                    $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                    Notification::make()
                        ->title('Invoice Approved')
                        ->body('Your signature has been recorded.')
                        ->success()
                        ->send();
                }),

            ActionGroup::make([
                EditAction::make(),
                Action::make('revise')
                    ->label('Revise / Return to Draft')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Invoice $record) => in_array($record->status, [InvoiceStatus::Submitted, InvoiceStatus::Approved]))
                    ->modalHeading('Revise Invoice')
                    ->modalDescription('This will move the Invoice back to Draft stage, allowing you to make changes. A revision snapshot will be created, and all existing signatures will be cleared.')
                    ->modalSubmitActionLabel('Start Revision')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for Revision')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $record->signatures()->delete();
                        $record->update(['status' => InvoiceStatus::Draft]);

                        Notification::make()
                            ->title('Invoice Revision Requested')
                            ->body('Reason: '.$data['reason'])
                            ->danger()
                            ->send();
                    }),
                $this->getExportPdfAction(),
                Action::make('sendEmail')
                    ->label(fn (Invoice $record) => $record->status === InvoiceStatus::Sent ? 'Resend Email' : 'Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->visible(fn (Invoice $record) => in_array($record->status, [InvoiceStatus::Approved, InvoiceStatus::Sent, InvoiceStatus::Partial, InvoiceStatus::Overdue]))
                    ->url(fn (Invoice $record) => InvoiceResource::getUrl('send', ['record' => $record])),
            ])
                ->label('Actions')
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Widgets\InvoiceAccrualSummaryWidget::class,
        ];
    }
}
