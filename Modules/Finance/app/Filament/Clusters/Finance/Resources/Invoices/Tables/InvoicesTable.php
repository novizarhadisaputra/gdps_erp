<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Tables;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Models\Invoice;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                    ),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('sendEmail')
                        ->label('Send Email')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->url(fn (Invoice $record) => \Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\InvoiceResource::getUrl('send', ['record' => $record])),
                    Actions\Action::make('pdf')
                        ->label('Export PDF')
                        ->color('gray')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(function (Invoice $record) {
                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance::pdf.invoice', ['record' => $record]);
                            $filename = str_replace(['/', '\\'], '-', $record->invoice_number);

                            return response()->streamDownload(fn () => print ($pdf->output()), "invoice-{$filename}.pdf");
                        }),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
