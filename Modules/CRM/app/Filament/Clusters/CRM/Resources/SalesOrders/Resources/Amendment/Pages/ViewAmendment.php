<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;
use Modules\CRM\Models\SalesOrderAmendment;

class ViewAmendment extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = AmendmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make([
                Action::make('approve')
                    ->label('Approve & Apply Amendment')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Amendment')
                    ->modalDescription('Approving this amendment will permanently update the parent Sales Order with the "Revised" data. This action cannot be undone.')
                    ->visible(fn (SalesOrderAmendment $record) => $record->status === SalesOrderAmendmentStatus::Draft)
                    ->action(function (SalesOrderAmendment $record) {
                        $record->update(['status' => SalesOrderAmendmentStatus::Approved]);

                        Notification::make()
                            ->title('Amendment Approved')
                            ->body('The parent Sales Order has been updated with the revised data via observer.')
                            ->success()
                            ->send();
                    }),

                Action::make('sendEmail')
                    ->label('Send Email to Customer')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('primary')
                    ->url(fn (SalesOrderAmendment $record) => AmendmentResource::getUrl('send', ['record' => $record, 'parent' => $record->sales_order_id])),

                Action::make('cancel')
                    ->label('Cancel Amendment')
                    ->icon(Heroicon::OutlinedXMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (SalesOrderAmendment $record) => $record->status === SalesOrderAmendmentStatus::Draft)
                    ->action(function (SalesOrderAmendment $record) {
                        $record->update(['status' => SalesOrderAmendmentStatus::Cancelled]);

                        Notification::make()
                            ->title('Amendment Cancelled')
                            ->danger()
                            ->send();
                    }),

                Action::make('pdf')
                    ->label('Export SOA PDF')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->color('gray')
                    ->action(function (SalesOrderAmendment $record) {
                        $pdf = Pdf::loadView('crm::pdf.sales-order-amendment', ['record' => $record]);
                        $filename = str_replace(['/', '\\'], '-', $record->amendment_number);

                        return response()->streamDownload(fn () => print ($pdf->output()), "soa-{$filename}.pdf");
                    }),
            ])
            ->label('More Actions')
            ->icon(Heroicon::EllipsisVertical)
            ->button(),
        ];
    }
}
