<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\HtmlString;
use Modules\CRM\Models\SalesOrder;

class SalesOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(3)
                    ->schema([
                        Section::make(__('General Information'))
                            ->description(__('Informasi dasar project dan dokumen referensi.'))
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('number')
                                            ->label(__('SO Number'))
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('order_date')
                                            ->date(),
                                        TextEntry::make('type')
                                            ->badge(),
                                        TextEntry::make('project.number')
                                            ->label(__('Referenced Project'))
                                            ->color('primary'),
                                        TextEntry::make('proposal.number')
                                            ->label(__('Referenced Proposal'))
                                            ->color('primary'),
                                        TextEntry::make('sourceable.number')
                                            ->label(__('Source Document'))
                                            ->color('primary')
                                            ->placeholder(__('-')),
                                        TextEntry::make('customer.name')
                                            ->label(__('Customer'))
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(2),

                        Section::make(__('Execution & PIC'))
                            ->schema([
                                TextEntry::make('salesPic.name')
                                    ->label(__('Sales PIC (AMS)')),
                                TextEntry::make('projectManager.name')
                                    ->label(__('Project Manager (Oprep)')),
                                TextEntry::make('service_type')
                                    ->label(__('Service Type')),
                                TextEntry::make('job_location')
                                    ->label(__('Job Location')),
                                TextEntry::make('manpower_initial_qty')
                                    ->label(__('Initial Manpower'))
                                    ->numeric()
                                    ->suffix(' Personnel'),
                            ])->columnSpan(1),
                    ])->columnSpanFull(),

                Section::make(__('Service Details (Snapshot)'))
                    ->description(__('Rincian komponen biaya dan personil pada saat Sales Order ini dibuat/diamandemen.'))
                    ->schema([
                        TextEntry::make('service_details_unified')
                            ->label(__(''))
                            ->view('crm::filament.components.combined-snapshot-table', fn (SalesOrder $record) => [
                                'items' => $record->content_config['items'] ?? [],
                                'manpower' => $record->content_config['manpower_details'] ?? [],
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('Financials & Terms'))

                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label(__('Total (Before Tax)'))
                                    ->money('IDR')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('grand_total_after_tax')
                                    ->label(__('Grand Total (After Tax)'))
                                    ->state(function (SalesOrder $record) {
                                        $subtotal = (float) $record->amount;
                                        $taxAmount = $record->tax ? $record->tax->calculateTax($subtotal) : floor($subtotal * (($record->tax_percentage ?? 12) / 100));

                                        return $subtotal + $taxAmount;
                                    })
                                    ->money('IDR')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('management_fee_percentage')
                                    ->label(__('Mgt. Fee Rate'))
                                    ->suffix('%'),
                                TextEntry::make('tax_percentage')
                                    ->label(__('Tax (VAT)'))
                                    ->suffix('%'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('payment_terms')
                                    ->label(__('Payment Terms'))
                                    ->state(fn (SalesOrder $record) => $record->payment_terms ?: ($record->content_config['payment_terms'] ?? '-')),
                                TextEntry::make('replacement_sla')
                                    ->label(__('Replacement SLA'))
                                    ->state(fn (SalesOrder $record) => $record->replacement_sla ?: ($record->content_config['replacement_sla'] ?? '-')),
                                TextEntry::make('probation_period')
                                    ->label(__('Probation Period'))
                                    ->state(fn (SalesOrder $record) => $record->probation_period ?: ($record->content_config['probation_period'] ?? '-')),
                                TextEntry::make('reporting_schedule')
                                    ->label(__('Reporting Schedule'))
                                    ->state(fn (SalesOrder $record) => $record->reporting_schedule ?: ($record->content_config['reporting_schedule'] ?? '-')),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('Attachments'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draft_so')
                                    ->label(__('Draft SO / Proposal Document'))
                                    ->state(function (SalesOrder $record) {
                                        $media = $record->getFirstMedia('draft_so');
                                        if (! $media) {
                                            return 'No draft document uploaded.';
                                        }

                                        return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Download Draft ({$media->file_name})</a>");
                                    })
                                    ->html(),
                                TextEntry::make('signed_so')
                                    ->label(__('Signed Sales Order Document'))
                                    ->state(function (SalesOrder $record) {
                                        $media = $record->getFirstMedia('signed_so');
                                        if (! $media) {
                                            return 'No signed document uploaded.';
                                        }

                                        return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Download Signed SO ({$media->file_name})</a>");
                                    })
                                    ->html(),
                            ]),
                    ])->columnSpanFull(),

                Section::make(__('Communication History'))
                    ->description(__('Traceability of emails sent to the customer regarding this Sales Order.'))
                    ->schema([
                        RepeatableEntry::make('communicationLogs')
                            ->label(__('Sent Emails'))
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('recipient_email')
                                        ->label(__('Recipient'))
                                        ->icon('heroicon-m-envelope'),
                                    TextEntry::make('subject')
                                        ->label(__('Subject')),
                                    TextEntry::make('sender.name')
                                        ->label(__('Sent By'))
                                        ->state(fn ($record) => $record->sender?->name ?? $record->sender_email ?? '-'),
                                    TextEntry::make('sent_at')
                                        ->label(__('Sent Date'))
                                        ->dateTime()
                                        ->color('gray'),
                                ]),
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull()
                    ->visible(fn ($record) => $record?->communicationLogs()->exists()),
            ]);
    }
}
