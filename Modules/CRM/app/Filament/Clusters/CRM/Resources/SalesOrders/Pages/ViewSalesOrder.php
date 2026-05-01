<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages;

use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\SalesOrderResource;
use Modules\CRM\Models\SalesOrder;
use Modules\MasterData\Models\Tax;
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
                    // Map content_config to BAPP items structure
                    $config = $record->content_config ?? [];
                    $bappItems = [];
                    $mfRate = (float) ($record->management_fee_percentage ?? 0);
                    $taxRate = (float) ($record->tax_percentage ?? Tax::where('category', 'sales')->where('is_default', true)->first()?->rate ?? 12);

                    // 1. Map Manpower
                    $totalCost = 0;
                    foreach ($config['manpower_details'] ?? [] as $mp) {
                        $sellingPrice = (float) ($mp['unit_price'] ?? 0);
                        $cost = (float) ($mp['unit_cost'] ?? 0);
                        $qty = (float) ($mp['quantity'] ?? 0);
                        $totalCost += ($cost * $qty);

                        if ($sellingPrice <= 0) {
                            $feeForThisItem = ($mfRate > 0) ? round(($cost / (1 - ($mfRate / 100))) - $cost, 0) : 0;
                            $sellingPrice = $cost + $feeForThisItem;
                        } else {
                            $feeForThisItem = ($sellingPrice - $cost);
                        }

                        $roundedUnitPrice = round($sellingPrice, 0);

                        $bappItems[] = [
                            'item_name' => $mp['job_position_name'] ?? 'Personnel',
                            'ukuran_pekerjaan' => $mp['job_position_name'] ?? '-',
                            'quantity' => $qty,
                            'uom' => $mp['uom'] ?? 'Person',
                            'unit_price' => $roundedUnitPrice,
                            'total_price' => round($roundedUnitPrice * $qty, 0),
                            'management_fee' => round($feeForThisItem * $qty, 0),
                            'so_reference' => $record->type->value === 'internal' ? '-' : $record->number,
                            'keterangan' => null,
                        ];
                    }

                    // 2. Map Operational Items
                    foreach ($config['items'] ?? [] as $item) {
                        $sellingPrice = (float) ($item['unit_price'] ?? 0);
                        $cost = (float) ($item['unit_cost'] ?? 0);
                        $qty = (float) ($item['quantity'] ?? 0);
                        $totalCost += ($cost * $qty);

                        if ($sellingPrice <= 0) {
                            $feeForThisItem = ($mfRate > 0) ? round(($cost / (1 - ($mfRate / 100))) - $cost, 0) : 0;
                            $sellingPrice = $cost + $feeForThisItem;
                        } else {
                            $feeForThisItem = ($sellingPrice - $cost);
                        }

                        $roundedUnitPrice = round($sellingPrice, 0);

                        $bappItems[] = [
                            'item_name' => $item['description'] ?? 'Item',
                            'ukuran_pekerjaan' => $item['description'] ?? '-',
                            'quantity' => $qty,
                            'uom' => $item['uom'] ?? 'Unit',
                            'unit_price' => $roundedUnitPrice,
                            'total_price' => round($roundedUnitPrice * $qty, 0),
                            'management_fee' => round($feeForThisItem * $qty, 0),
                            'so_reference' => $record->type->value === 'internal' ? '-' : $record->number,
                            'keterangan' => null,
                        ];
                    }

                    $totalItemsAmount = collect($bappItems)->sum('total_price');
                    $feeAmount = collect($bappItems)->sum('management_fee');

                    $vatLabelId = $config['vat_label'] ?? 'PPN';
                    $vatLabelEn = $config['vat_label'] ?? 'VAT';
                    $bapp = WorkCompletionReport::create([
                        'project_id' => $record->project_id,
                        'sourceable_id' => $record->id,
                        'sourceable_type' => $record->getMorphClass(),
                        'customer_id' => $record->customer_id,
                        'number' => 'Auto-generated',
                        'tax_id' => $record->tax_id,
                        'items' => [
                            'id' => $bappItems,
                            'en' => $bappItems,
                        ],
                        'total_amount' => round($totalItemsAmount, 0),
                        'tax_percentage' => $record->type->value === 'internal' ? 0 : $taxRate,
                        'tax_basis' => 'total',
                        'tax_base_amount' => round($totalItemsAmount, 0),
                        'tax_amount' => round($totalItemsAmount * ($taxRate / 100), 0),
                        'tax_wording' => $record->type->value === 'internal'
                            ? ['id' => '-', 'en' => '-']
                            : [
                                'id' => "Penyelesaian pekerjaan di atas belum termasuk {$vatLabelId} {$taxRate}%",
                                'en' => "The above work completion does not include {$taxRate}% {$vatLabelEn}",
                            ],
                        'document_date' => now(),
                        'service_period_start' => now()->startOfMonth(),
                        'service_period_end' => now()->endOfMonth(),
                        'work_progress_percentage' => 100,
                        'status' => WorkCompletionStatus::Draft,
                        'description' => [
                            'id' => "Laporan Penyelesaian Pekerjaan Bulanan untuk SO {$record->number}",
                            'en' => "Monthly Work Completion Report for SO {$record->number}",
                        ],
                        'content_config' => [
                            'management_fee_percentage' => $mfRate,
                            'management_fee_amount' => $feeAmount,
                            'total_cost' => $totalCost,
                        ],
                    ]);

                    Notification::make()
                        ->title('BAPP Draft Created')
                        ->body("New BAPP {$bapp->number} has been successfully generated.")
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
                    ->visible(fn (SalesOrder $record) => $record->type === SalesOrderType::External &&
                        in_array($record->status, [SalesOrderStatus::Draft, SalesOrderStatus::Submitted]) &&
                        (
                            $record->hasMedia('draft_so') ||
                            ($record->proposal?->hasMedia('signed_proposal') ?? false)
                        ) &&
                        ($record->profitabilityAnalysis?->is_margin_approved ?? false)
                    )
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
                        // 1. Validate Tax
                        if (! $record->tax_percentage) {
                            Notification::make()
                                ->title('Incomplete Financial Data')
                                ->body('Please ensure the Tax Percentage is set before exporting.')
                                ->danger()
                                ->send();

                            return;
                        }

                        // 2. Validate Data Source for Internal SO
                        if ($record->type === SalesOrderType::Internal && ! $record->sourceable_id) {
                            Notification::make()
                                ->title('Missing Source Document')
                                ->body('Internal Sales Orders must reference a Source Document (PO/SPK/PKS).')
                                ->danger()
                                ->send();

                            return;
                        }

                        // 3. Validate Items Content
                        $config = $record->content_config ?? [];
                        if (empty($config['items'] ?? []) && empty($config['manpower_details'] ?? [])) {
                            Notification::make()
                                ->title('No Items Found')
                                ->body('This Sales Order has no line items. Please select a Project reference to retrieve data.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $pdf = Pdf::loadView('crm::pdf.sales-order', ['record' => $record]);
                        $name = str_replace(['/', '\\'], '-', $record->number);
                        $clientName = $record->customer?->name
                            ?? $record->proposal?->lead?->company_name
                            ?? $record->proposal?->lead?->title
                            ?? 'Client';
                        $slugName = Str::slug($clientName, '-');
                        $fileName = "SO_{$name}_{$slugName}.pdf";

                        return response()->streamDownload(fn () => print ($pdf->output()), $fileName);
                    }),
            ])
                ->label('Options')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->color('primary')
                ->button(),
        ];
    }
}
