<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\{Action, ActionGroup, EditAction};
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;
use Modules\CRM\Models\SalesOrder;
use Modules\MasterData\Services\SignatureService;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft),

            ActionGroup::make([
                Action::make('sendEmail')
                    ->label('Send Email')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft)
                    ->url(fn (SalesOrder $record) => SalesOrderResource::getUrl('send', ['record' => $record])),

                Action::make('submit')
                    ->label('Submit')
                    ->color('info')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft)
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => SalesOrderStatus::Submitted]);
                        app(SignatureService::class)->notifyNextApprovers($record);
                        Notification::make()->title('Order Submitted for Approval')->success()->send();
                    }),

                Action::make('approve')
                    ->label('Approve Order')
                    ->color('success')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrder $record) => in_array($record->status, [SalesOrderStatus::Draft, SalesOrderStatus::Submitted]))
                    ->action(function (SalesOrder $record) {
                        $record->update(['status' => SalesOrderStatus::Approved]);
                        Notification::make()->title('Order Approved')->success()->send();
                    }),

                Action::make('revisi')
                    ->label('Request Revision')
                    ->color('warning')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->visible(fn (SalesOrder $record) => in_array($record->status, [SalesOrderStatus::Submitted, SalesOrderStatus::Approved]))
                    ->action(function (SalesOrder $record) {
                        if ($record->status === SalesOrderStatus::Approved) {
                            Notification::make()
                                ->title('Redirecting to Amendments')
                                ->body('For approved orders, revisions must be managed via Amendments. If this is a financial change, please revise the Profitability Analysis first.')
                                ->info()
                                ->send();

                            return redirect()->to(SalesOrderResource::getUrl('amendments', ['record' => $record]));
                        }

                        // For Sent status, we can still revert to Draft for simple fixes
                        $record->update(['status' => SalesOrderStatus::Draft]);
                        Notification::make()
                            ->title('Order Reverted to Draft')
                            ->body('This document is now editable for revision.')
                            ->warning()
                            ->send();
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
                        $pdf = Pdf::loadView('crm::pdf.sales-order', ['record' => $record]);
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
