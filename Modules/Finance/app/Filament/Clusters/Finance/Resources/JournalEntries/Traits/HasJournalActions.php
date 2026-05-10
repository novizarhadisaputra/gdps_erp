<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\JournalEntries\Traits;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Modules\Finance\Models\JournalEntry;

trait HasJournalActions
{
    protected function getPrintVoucherAction(): Action
    {
        return Action::make('printVoucher')
            ->label('Print Voucher')
            ->color('gray')
            ->icon('heroicon-o-printer')
            ->action(function (JournalEntry $record) {
                $logo = 'data:image/png;base64,'.base64_encode(file_get_contents(public_path('images/branding/header_left.png')));

                $sourceType = '-';
                if ($record->reference_type) {
                    $sourceType = class_basename($record->reference_type);
                }

                $pdf = Pdf::loadView('finance::pdf.journal-voucher', [
                    'record' => $record,
                    'logo' => $logo,
                    'sourceType' => $sourceType,
                ]);

                $name = str_replace(['/', '\\'], '-', $record->number);
                $fileName = "Voucher-{$name}.pdf";

                return response()->streamDownload(
                    fn () => print ($pdf->output()),
                    $fileName
                );
            });
    }
}
