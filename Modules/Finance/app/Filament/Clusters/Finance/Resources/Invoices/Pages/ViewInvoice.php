<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Models\Invoice;
use Modules\MasterData\Services\SignatureService;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('approveInvoice')
                ->label('Approve Invoice')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(function (Invoice $record) {
                    if ($record->status !== InvoiceStatus::Draft) {
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

                    if ($record->isFullyApproved()) {
                        $record->update(['status' => InvoiceStatus::Approved]);
                    }

                    $service->notifyNextApprovers($record);
                    $service->notifyOwnerOnSignature($record, auth()->user(), 'Approver');

                    Notification::make()
                        ->title('Invoice Approved')
                        ->body('Your signature has been recorded.')
                        ->success()
                        ->send();
                }),
                Action::make('rejectInvoice')
                    ->label('Reject / Revision')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Invoice $record) => $record->status === InvoiceStatus::Draft && ! $record->isFullyApproved())
                    ->modalHeading('Request Revision')
                    ->modalDescription('Please provide a reason for rejecting this Invoice. The creator will be notified to revise it.')
                    ->modalSubmitActionLabel('Submit Revision Request')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for Revision')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        // Reset any partial signatures
                        $record->signatures()->delete();
                        
                        // Keep status as Draft, but notify the owner
                        // Assuming $record->salesOrder->user_id is the owner, or similar. If unknown, we can just log it or notify all Finance users.
                        Notification::make()
                            ->title('Invoice Revision Requested')
                            ->body("Reason: " . $data['reason'])
                            ->danger()
                            ->send();
                            
                        // Also broadcast to the user who created it (if you have an owner tracking on invoice)
                        // This uses Filament's Database Notifications if configured
                        // Notification::make()->title('Invoice Rejected')->body($data['reason'])->sendToDatabase($record->creator);
                    }),
            Action::make('markAsPaid')
                ->label('Mark as Paid')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (Invoice $record) => in_array($record->status, [InvoiceStatus::Sent, InvoiceStatus::Partial, InvoiceStatus::Overdue]))
                ->schema([
                    SpatieMediaLibraryFileUpload::make('payment_proof')
                        ->collection('payment_proof')
                        ->label('Bukti Pembayaran (Payment Proof)')
                        ->required()
                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                        ->maxSize(5120),
                ])
                ->action(function (Invoice $record) {
                    $record->update(['status' => InvoiceStatus::Paid]);
                    Notification::make()
                        ->title('Invoice Marked as Paid')
                        ->success()
                        ->send();
                }),
            EditAction::make(),
            ActionGroup::make([
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function (Invoice $record) {
                        $pdf = Pdf::loadView('finance::pdf.invoice', ['record' => $record]);
                        $filename = str_replace(['/', '\\'], '-', $record->invoice_number);

                        return response()->streamDownload(
                            fn () => print ($pdf->output()),
                            "invoice-{$filename}.pdf"
                        );
                    }),
                Action::make('sendEmail')
                    ->label(fn (Invoice $record) => $record->status === InvoiceStatus::Sent ? 'Resend Email' : 'Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('primary')
                    ->visible(fn (Invoice $record) => in_array($record->status, [InvoiceStatus::Approved, InvoiceStatus::Sent, InvoiceStatus::Partial, InvoiceStatus::Overdue]))
                    ->url(fn (Invoice $record) => InvoiceResource::getUrl('send', ['record' => $record])),
            ])
                ->label('Options')
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->button(),
        ];
    }
}
