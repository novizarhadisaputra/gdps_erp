<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource;
use Modules\Finance\Models\Invoice;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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
        ];
    }
}
