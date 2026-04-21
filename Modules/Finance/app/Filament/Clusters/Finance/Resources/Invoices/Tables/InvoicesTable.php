<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
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
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('sendEmail')
                    ->label('Send Email')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        try {
                            $signatureUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                'invoices.public.sign',
                                now()->addDays(7),
                                ['invoice' => $record->id]
                            );

                            $messageBody = "Please review and sign Invoice #{$record->invoice_number} by clicking the link below:<br><br>";
                            $messageBody .= "<a href='{$signatureUrl}' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Sign Invoice Online</a>";

                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'content-type' => 'application/json',
                                'x-requester-app' => 'GDPS-ERP',
                            ])->post('https://machine.garudapratama.com/api/v1/email/send', [
                                'to' => [$record->customer?->email],
                                'subject' => "Invoice - {$record->invoice_number}",
                                'body' => $messageBody,
                            ]);

                            if (! $response->successful()) {
                                throw new \Exception('External API Error: '.$response->status());
                            }

                            $record->update(['status' => \Modules\Finance\Enums\InvoiceStatus::Sent]);

                            \Filament\Notifications\Notification::make()
                                ->title('Email Sent')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Failed to Send Email')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('pdf')
                    ->label('Export PDF')
                    ->color('gray')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->action(function (Invoice $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance::pdf.invoice', ['record' => $record]);
                        $filename = str_replace(['/', '\\'], '-', $record->invoice_number);

                        return response()->streamDownload(fn () => print ($pdf->output()), "invoice-{$filename}.pdf");
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
