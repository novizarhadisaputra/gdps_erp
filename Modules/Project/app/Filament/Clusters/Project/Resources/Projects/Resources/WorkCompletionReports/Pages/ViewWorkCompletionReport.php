<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Enums\InvoiceStatus;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Models\Invoice;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\WorkCompletionReport;

class ViewWorkCompletionReport extends ViewRecord
{
    protected static string $resource = WorkCompletionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('generateInvoice')
                ->label('Generate Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('success')
                ->visible(fn (WorkCompletionReport $record) => in_array($record->status->value ?? $record->status, ['verified', 'signed']))
                ->requiresConfirmation()
                ->modalHeading('Generate Invoice')
                ->modalDescription('This will create a new draft Invoice based on this BAPP work progress.')
                ->action(function (WorkCompletionReport $record) {
                    $amount = collect($record->items)->sum('total_price');
                    $taxRate = $record->salesOrder?->tax_percentage ?? 11;
                    $taxAmount = $amount * ($taxRate / 100);
                    $totalAmount = $amount + $taxAmount;

                    // Standardized Invoice Number: GDPS/UB/INV-XXX/YY (Global annual sequence)
                    $sequence = Invoice::whereBetween('invoice_date', [now()->startOfYear(), now()->endOfYear()])->count() + 1;
                    $invoiceNumber = sprintf('GDPS/UB/INV-%03d/%s', $sequence, now()->format('y'));

                    $invoice = Invoice::create([
                        'sales_order_id' => $record->sales_order_id,
                        'work_completion_report_id' => $record->id,
                        'customer_id' => $record->customer_id,
                        'invoice_number' => $invoiceNumber,
                        'invoice_date' => now(),
                        'due_date' => now()->addDays(30),
                        'amount' => $amount,
                        'tax_amount' => $taxAmount,
                        'total_amount' => $totalAmount,
                        'status' => InvoiceStatus::Draft,
                    ]);

                    Notification::make()
                        ->title('Invoice Draft Created')
                        ->success()
                        ->send();

                    return redirect()->to(InvoiceResource::getUrl('edit', ['record' => $invoice->id]));
                }),
            Action::make('pdf')
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
                }),
        ];
    }
}
