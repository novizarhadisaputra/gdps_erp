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

                $name = str_replace(['/', '\\'], '-', $record->number);
                $customerName = \Illuminate\Support\Str::slug($record->customer?->company_name ?? $record->customer?->name ?? 'Unknown-Customer', '-');
                $langSuffix = strtoupper($data['language']);
                $fileName = "Invoice_{$name}_{$customerName}_{$langSuffix}.pdf";

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    $fileName
                );
            });
    }
}
