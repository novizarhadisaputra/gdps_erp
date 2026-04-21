<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;
use Modules\CRM\Models\SalesOrder;
use Modules\MasterData\Services\SignatureService;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\WorkCompletionReport;

class ViewSalesOrder extends ViewRecord
{
    protected static string $resource = SalesOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Draft),

            Action::make('generateBapp')
                ->label('Generate BAPP')
                ->icon(Heroicon::DocumentCheck)
                ->color('success')
                ->visible(fn (SalesOrder $record) => $record->status === SalesOrderStatus::Approved)
                ->requiresConfirmation()
                ->modalHeading('Generate Monthly BAPP')
                ->modalDescription('This will create a new Work Completion Report draft based on this Sales Order.')
                ->action(function (SalesOrder $record) {
                    $sequence = WorkCompletionReport::where('sales_order_id', $record->id)->count() + 1;
                    $reportNumber = sprintf('GDPS/UB/BAPP-%03d/%02d/%s', $record->sequence_number, $sequence, now()->format('y'));

                    // Map content_config to BAPP items structure
                    $config = $record->content_config ?? [];
                    $bappItems = [];

                    // 1. Map Manpower
                    foreach ($config['manpower_details'] ?? [] as $mp) {
                        $bappItems[] = [
                            'item_name' => $mp['job_position_name'] ?? 'Personnel',
                            'quantity' => $mp['quantity'] ?? 0,
                            'uom' => $mp['uom'] ?? 'Person',
                            'unit_price' => $mp['unit_cost'] ?? 0,
                            'total_price' => $mp['total_monthly_cost'] ?? 0,
                            'so_reference' => $record->so_number,
                        ];
                    }

                    // 2. Map Operational Items
                    foreach ($config['items'] ?? [] as $item) {
                        $bappItems[] = [
                            'item_name' => $item['description'] ?? 'Item',
                            'quantity' => $item['quantity'] ?? 0,
                            'uom' => $item['uom'] ?? 'Unit',
                            'unit_price' => $item['unit_price'] ?? 0,
                            'total_price' => $item['total_price'] ?? 0,
                            'so_reference' => $record->so_number,
                        ];
                    }

                    $bapp = WorkCompletionReport::create([
                        'project_id' => $record->project_id,
                        'sales_order_id' => $record->id,
                        'customer_id' => $record->customer_id,
                        'report_number' => $reportNumber,
                        'items' => $bappItems,
                        'document_date' => now(),
                        'service_period_start' => now()->startOfMonth(),
                        'service_period_end' => now()->endOfMonth(),
                        'status' => WorkCompletionStatus::Draft,
                        'description' => "Monthly BAPP #{$sequence} for SO {$record->so_number}",
                    ]);

                    Notification::make()
                        ->title('BAPP Draft Created')
                        ->body("New BAPP {$reportNumber} has been successfully generated.")
                        ->success()
                        ->actions([
                            Action::make('view')
                                ->label('View BAPP')
                                ->button()
                                ->url(WorkCompletionReportResource::getUrl('edit', [
                                    'project' => $record->project_id,
                                    'record' => $bapp->id,
                                ])),
                        ])
                        ->send();

                    return redirect()->to(WorkCompletionReportResource::getUrl('edit', [
                        'project' => $record->project_id,
                        'record' => $bapp->id,
                    ]));
                }),

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
