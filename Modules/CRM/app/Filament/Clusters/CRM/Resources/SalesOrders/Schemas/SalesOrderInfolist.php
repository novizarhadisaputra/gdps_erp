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
                        RepeatableEntry::make('content_config.items')
                            ->label('Items & Pricing')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('description')->columnSpan(1),
                                        TextEntry::make('quantity')->numeric()->columnSpan(1),
                                        TextEntry::make('uom')->label('UoM')->columnSpan(1),
                                        TextEntry::make('total_price')
                                            ->money('IDR')
                                            ->label('Total/Month')
                                            ->weight(FontWeight::Bold)
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->columnSpanFull(),

                        RepeatableEntry::make('content_config.manpower_details')
                            ->label('Staffing Composition')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('job_position_name')->label('Position'),
                                        TextEntry::make('quantity')->numeric()->label('Qty'),
                                        TextEntry::make('total_monthly_cost')->money('IDR')->label('Est. Monthly Cost'),
                                    ]),
                            ])
                            ->columnSpanFull(),
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
                        TextEntry::make('signed_so')
                            ->label('Signed Sales Order Document')
                            ->state(function (SalesOrder $record) {
                                $media = $record->getFirstMedia('signed_so');
                                if (! $media) {
                                    return 'No document uploaded.';
                                }

                                return new HtmlString("<a href='{$media->getUrl()}' target='_blank' class='text-primary-600 font-bold underline flex items-center gap-1'>Download Signed SO ({$media->file_name})</a>");
                            })
                            ->html(),
                    ])->columnSpanFull(),
            ]);
    }
}
