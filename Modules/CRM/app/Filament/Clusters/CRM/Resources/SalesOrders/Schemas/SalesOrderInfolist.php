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
                        Section::make('General Information')
                            ->description('Informasi dasar project dan dokumen referensi.')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('so_number')
                                            ->label('SO Number')
                                            ->weight(FontWeight::Bold)
                                            ->copyable(),
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('order_date')
                                            ->date(),
                                        TextEntry::make('type')
                                            ->badge(),
                                        TextEntry::make('project.code')
                                            ->label('Referenced Project')
                                            ->color('primary'),
                                        TextEntry::make('proposal.proposal_number')
                                            ->label('Referenced Proposal')
                                            ->color('primary'),
                                        TextEntry::make('customer.name')
                                            ->label('Customer')
                                            ->columnSpanFull(),
                                    ]),
                            ])->columnSpan(2),

                        Section::make('Execution & PIC')
                            ->schema([
                                TextEntry::make('salesPic.name')
                                    ->label('Sales PIC (AMS)'),
                                TextEntry::make('projectManager.name')
                                    ->label('Project Manager (Oprep)'),
                                TextEntry::make('service_type')
                                    ->label('Service Type'),
                                TextEntry::make('job_location')
                                    ->label('Job Location'),
                                TextEntry::make('manpower_initial_qty')
                                    ->label('Initial Manpower')
                                    ->numeric()
                                    ->suffix(' Personnel'),
                            ])->columnSpan(1),
                    ])->columnSpanFull(),

                Section::make('Service Details (Snapshot)')
                    ->description('Rincian komponen biaya dan personil pada saat Sales Order ini dibuat/diamandemen.')
                    ->schema([
                        TextEntry::make('service_details_unified')
                            ->label('')
                            ->view('crm::filament.components.combined-snapshot-table', fn (SalesOrder $record) => [
                                'items' => $record->content_config['items'] ?? [],
                                'manpower' => $record->content_config['manpower_details'] ?? [],
                            ]),
                    ])->columnSpanFull(),

                Section::make('Financials & Terms')

                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Grand Total / Month')
                                    ->money('IDR')
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                                TextEntry::make('management_fee_percentage')
                                    ->label('Mgt. Fee Rate')
                                    ->suffix('%'),
                                TextEntry::make('tax_percentage')
                                    ->label('Tax (VAT)')
                                    ->suffix('%'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('payment_terms')->label('Payment Terms'),
                                TextEntry::make('replacement_sla')->label('Replacement SLA'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Attachments')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draft_so')
                                    ->label('Draft SO / Proposal Document')
                                    ->state(function (SalesOrder $record) {
                                        $media = $record->getFirstMedia('draft_so');
                                        if (! $media) {
                                            return 'No draft document uploaded.';
                                        }

                                        return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Download Draft ({$media->file_name})</a>");
                                    })
                                    ->html(),
                                TextEntry::make('signed_so')
                                    ->label('Signed Sales Order Document')
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

                Section::make('Communication History')
                    ->description('Traceability of emails sent to the customer regarding this Sales Order.')
                    ->schema([
                        RepeatableEntry::make('communicationLogs')
                            ->label('Sent Emails')
                            ->schema([
                                Grid::make(4)->schema([
                                    TextEntry::make('recipient_email')
                                        ->label('Recipient')
                                        ->icon('heroicon-m-envelope'),
                                    TextEntry::make('subject')
                                        ->label('Subject'),
                                    TextEntry::make('sender.name')
                                        ->label('Sent By')
                                        ->state(fn ($record) => $record->sender?->name ?? $record->sender_email ?? '-'),
                                    TextEntry::make('sent_at')
                                        ->label('Sent Date')
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
