<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Models\Invoice;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource as ClusterResource;
use Modules\Project\Models\WorkCompletionReport;

trait HasWorkCompletionReportActions
{
    protected function getWorkCompletionReportHeaderActions(): array
    {
        return [
            EditAction::make(),

            ActionGroup::make([
                $this->getExportPdfAction(),
                $this->getGenerateInvoiceAction(),
                $this->getDiscussionsAction(),
            ])
                ->label('Options')
                ->icon('heroicon-m-cog-6-tooth')
                ->color('gray')
                ->button(),

            ActionGroup::make([
                $this->getSubmitAction(),
                $this->getSendToCustomerAction(),
                $this->getResendEmailAction(),
                $this->getApproveAction(),
                $this->getRejectAction(),
            ])
                ->label('Workflow')
                ->icon('heroicon-m-chevron-down')
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
            ->action(function (WorkCompletionReport $record) {
                $pdf = Pdf::loadView('project::pdf.work_completion_report', ['record' => $record]);
                $filename = str_replace(['/', '\\'], '-', $record->report_number);

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    "bapp-{$filename}.pdf"
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

    protected function getGenerateInvoiceAction(): Action
    {
        return Action::make('generateInvoice')
            ->label('Generate Invoice')
            ->icon('heroicon-o-document-plus')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Confirm Invoice Generation')
            ->modalDescription('This will generate a draft invoice based on this Work Completion Report.')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Approved && ! $record->invoices()->exists())
            ->action(function (WorkCompletionReport $record) {
                $totalAmount = collect($record->items)->sum('total_price');

                $invoice = Invoice::create([
                    'sales_order_id' => $record->sales_order_id,
                    'customer_id' => $record->customer_id,
                    'work_completion_report_id' => $record->id,
                    'invoice_number' => 'Auto-generated',
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(30),
                    'amount' => $totalAmount,
                    'tax_amount' => $totalAmount * 0.11,
                    'total_amount' => $totalAmount * 1.11,
                    'status' => InvoiceStatus::Draft,
                    'items' => $record->items,
                ]);

                Notification::make()
                    ->title('Invoice Generated Successfully')
                    ->success()
                    ->send();

                return redirect()->to(InvoiceResource::getUrl('edit', ['record' => $invoice]));
            });
    }

    protected function getSubmitAction(): Action
    {
        return Action::make('submit')
            ->label('Submit for Review')
            ->color('primary')
            ->icon('heroicon-o-paper-airplane')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Draft)
            ->requiresConfirmation()
            ->action(function (WorkCompletionReport $record) {
                $record->update(['status' => WorkCompletionStatus::Submitted]);
                Notification::make()->title('BAPP Submitted for Review')->success()->send();
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
            ->label('Approve BAPP (Confirm Signature)')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->visible(fn (WorkCompletionReport $record) => $record->status === WorkCompletionStatus::Sent && $record->hasMedia('signed_report'))
            ->requiresConfirmation()
            ->modalHeading('Confirm BAPP Approval')
            ->modalDescription('By approving this BAPP, you confirm that you have received and verified the Signed BAPP (Final Scan) from the customer.')
            ->action(function (WorkCompletionReport $record) {
                if (! $record->hasMedia('signed_report')) {
                    Notification::make()
                        ->title('Proof of Signature Missing')
                        ->body('Please upload the Signed BAPP (Final Scan) before approving.')
                        ->warning()
                        ->send();

                    return;
                }

                $record->update(['status' => WorkCompletionStatus::Approved]);
                Notification::make()->title('BAPP Approved Successfully')->success()->send();
            });
    }

    protected function getRejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->visible(fn (WorkCompletionReport $record) => in_array($record->status, [
                WorkCompletionStatus::Submitted,
                WorkCompletionStatus::Sent,
            ]))
            ->requiresConfirmation()
            ->action(function (WorkCompletionReport $record) {
                $record->update(['status' => WorkCompletionStatus::Rejected]);
                Notification::make()->title('BAPP Rejected')->danger()->send();
            });
    }
}
