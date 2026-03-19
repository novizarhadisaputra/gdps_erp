<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Tables;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Models\SalesOrder;

class SalesOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('so_number')
                    ->label('SO Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.code')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name'),
                SelectFilter::make('status')
                    ->options(SalesOrderStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('sendEmail')
                    ->label('Send Email')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->action(function (SalesOrder $record) {
                        try {
                            $signatureUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                                'sales_orders.public.sign',
                                now()->addDays(7),
                                ['sales_order' => $record->id]
                            );

                            $messageBody = "Please review and sign Sales Order #{$record->so_number} by clicking the link below:<br><br>";
                            $messageBody .= "<a href='{$signatureUrl}' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Sign Sales Order Online</a>";

                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'content-type' => 'application/json',
                                'x-requester-app' => 'GDPS-ERP',
                            ])->post('https://machine.garudapratama.com/api/v1/email/send', [
                                'to' => [$record->customer?->email],
                                'subject' => "Sales Order - {$record->so_number}",
                                'body' => $messageBody,
                            ]);

                            if (! $response->successful()) {
                                throw new \Exception('External API Error: '.$response->status());
                            }

                            $record->update(['status' => SalesOrderStatus::Sent]);

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
                    ->action(function (SalesOrder $record) {
                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('crm::pdf.sales-order', ['record' => $record]);
                        $filename = str_replace(['/', '\\'], '-', $record->so_number);

                        return response()->streamDownload(fn () => print ($pdf->output()), "so-{$filename}.pdf");
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
