<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Modules\Finance\Models\Invoice;

trait HasInvoiceActions
{
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
            ->action(function (Invoice $record, array $data) {
                app()->setLocale($data['language']);

                $pdf = Pdf::loadView('finance::pdf.invoice', [
                    'record' => $record,
                    'language' => $data['language'],
                ]);

                $filename = str_replace(['/', '\\'], '-', $record->number);
                $langSuffix = strtoupper($data['language']);

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    "invoice-{$filename}-{$langSuffix}.pdf"
                );
            });
    }
}
