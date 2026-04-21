<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\{Action, ActionGroup, EditAction};
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\SalesOrderAmendmentStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\MasterData\Services\SignatureService;

class ViewAmendment extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = AmendmentResource::class;
 
     protected function mutateFormDataBeforeFill(array $data): array
     {
         $after = $data['after_snapshot'] ?? [];
         $unified = [];
 
         // Add Items
         foreach ($after['items'] ?? [] as $item) {
             $unified[] = array_merge($item, ['type' => 'item']);
         }
 
         // Add Manpower
         foreach ($after['manpower_details'] ?? [] as $mp) {
             $unified[] = array_merge($mp, [
                 'type' => 'personnel',
                 'description' => $mp['job_position_name'] ?? '',
             ]);
         }
 
         $data['after_snapshot_unified'] = $unified;
 
         return $data;
     }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (SalesOrderAmendment $record) => $record->status === SalesOrderAmendmentStatus::Draft),

            Action::make('submit')
                ->label('Submit')
                ->color('info')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->requiresConfirmation()
                ->visible(fn (SalesOrderAmendment $record) => $record->status === SalesOrderAmendmentStatus::Draft)
                ->action(function (SalesOrderAmendment $record) {
                    $record->update(['status' => SalesOrderAmendmentStatus::Submitted]);
                    app(SignatureService::class)->notifyNextApprovers($record);
                    Notification::make()->title('Amendment Submitted for Approval')->success()->send();
                }),

            ActionGroup::make([
                Action::make('sendEmail')
                    ->label('Send Email to Customer')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->url(fn (SalesOrderAmendment $record) => AmendmentResource::getUrl('send', ['record' => $record, 'sales_order' => $record->sales_order_id])),

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
