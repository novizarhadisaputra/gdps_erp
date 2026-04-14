<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft),

            \Filament\Actions\ActionGroup::make([
                Action::make('sendEmail')
                    ->label('Send Email')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft)
                    ->action(function (SalesOrder $record) {
                        try {
                            $signatureUrl = URL::temporarySignedRoute(
                                'sales_orders.public.sign',
                                now()->addDays(7),
                                ['sales_order' => $record->id]
                            );

                            $messageBody = "Please review and sign Sales Order #{$record->so_number} by clicking the link below:<br><br>";
                            $messageBody .= "<a href='{$signatureUrl}' style='display: inline-block; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>Sign Sales Order Online</a>";

                            $response = Http::withHeaders([
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

                            Notification::make()
                                ->title('Email Sent')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to Send Email')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Approve Order')
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrder $record) => in_array($record->status, [SalesOrderStatus::Draft, SalesOrderStatus::Sent]))
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => SalesOrderStatus::Approved]);
                        Notification::make()->title('Order Approved')->success()->send();
                    }),

                Action::make('revisi')
                    ->label('Revisi')
                    ->color('warning')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Revisi')
                            ->required()
                            ->placeholder('Contoh: Koreksi jumlah manpower atau perubahan term pembayaran.'),
                    ])
                    ->visible(fn (SalesOrder $record) => in_array($record->status, [SalesOrderStatus::Sent, SalesOrderStatus::Approved]))
                    ->action(function (SalesOrder $record, array $data) {
                        if ($record->status === SalesOrderStatus::Approved) {
                            // Create Amendment Snapshot
                            SalesOrderAmendment::create([
                                'sales_order_id' => $record->id,
                                'amendment_date' => now(),
                                'reason' => $data['reason'],
                                'before_snapshot' => [
                                    'items' => $record->content_config['items'] ?? [],
                                    'manpower_details' => $record->content_config['manpower_details'] ?? [],
                                ],
                                'status' => 'approved',
                            ]);
                        }

                        $record->update(['status' => SalesOrderStatus::Draft]);
                        Notification::make()->title('Order returned to Draft for Revision')->warning()->send();
                    }),

                Action::make('cancel')
                    ->label('Cancel Order')
                    ->color('danger')
                    ->icon(Heroicon::OutlinedNoSymbol)
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrder $record) => ! in_array($record->status, [SalesOrderStatus::Cancelled]))
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => SalesOrderStatus::Cancelled]);
                        Notification::make()->title('Order Cancelled')->danger()->send();
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
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('primary')
                ->button(),
        ];
    }
}
