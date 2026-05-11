<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;

class WorkCompletionReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Documents')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('draft_report')
                                    ->label('Draft BAPP (Unsigned)')
                                    ->state(fn ($record) => $record->getFirstMedia('draft_report')?->file_name ?? 'No Draft')
                                    ->url(fn ($record) => $record->getFirstMedia('draft_report')?->getUrl(), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Draft' ? 'gray' : 'primary'),

                                TextEntry::make('signed_report')
                                    ->label('Signed BAPP (Final Scan)')
                                    ->state(fn ($record) => $record->getFirstMedia('signed_report')?->file_name ?? 'No Scan Uploaded')
                                    ->url(fn ($record) => $record->getFirstMedia('signed_report')?->getUrl(), true)
                                    ->icon(Heroicon::OutlinedCheckBadge)
                                    ->color(fn ($state) => $state === 'No Scan Uploaded' ? 'gray' : 'success'),
                            ]),
                    ])->columnSpanFull()->collapsible(),

                Section::make('Report Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('number')
                                    ->label('BAPP Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('document_date')
                                    ->label('Document Date')
                                    ->date(),
                                TextEntry::make('project.name')
                                    ->label('Project'),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('sourceable.number')
                                    ->label('Reference Document'),
                            ]),
                    ])->columnSpanFull()->collapsible(),

                Section::make('Customer Signatory')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('content_config.recipient_name')
                                    ->label('Recipient Name')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('content_config.recipient_title')
                                    ->label('Recipient Title/Position'),
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                    ])->columnSpanFull()->collapsible(),

                Section::make('Service & Progress')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('service_period_start')
                                    ->label('Start Period')
                                    ->date(),
                                TextEntry::make('service_period_end')
                                    ->label('End Period')
                                    ->date(),
                                TextEntry::make('work_progress_percentage')
                                    ->label('Progress')
                                    ->suffix('%')
                                    ->color('info')
                                    ->weight(FontWeight::Bold),
                            ]),
                    ])->columnSpanFull()->collapsible(),

                Section::make('BAPP Line Items')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('work_measurement')
                                            ->label('Description')
                                            ->columnSpan(2),
                                        TextEntry::make('quantity')
                                            ->label('Qty'),
                                        TextEntry::make('uom')
                                            ->label('Unit'),
                                        TextEntry::make('unit_price')
                                            ->label('Price')
                                            ->money('IDR'),
                                        TextEntry::make('total_price')
                                            ->label('Total')
                                            ->money('IDR')
                                            ->weight(FontWeight::Bold),
                                    ]),
                            ]),
                    ])->columnSpanFull()->collapsible(),

                Section::make('Total & Tax')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('total_amount')
                                    ->label('Subtotal')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('tax_base_amount')
                                    ->label('Taxable Base (DPP)')
                                    ->money('IDR')
                                    ->color('gray'),
                                TextEntry::make('tax_amount')
                                    ->label('VAT (PPN)')
                                    ->money('IDR')
                                    ->color('warning')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('grand_total')
                                    ->label('Grand Total')
                                    ->state(fn ($record) => (float) $record->total_amount + (float) $record->tax_amount)
                                    ->money('IDR')
                                    ->weight(FontWeight::Black)
                                    ->size(TextSize::Large)
                                    ->color('primary')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Digital Signatures')
                    ->schema([
                        DigitalSignatureEntry::make('signatures')->columnSpanFull(),
                    ])->columnSpanFull()->collapsible(),
            ]);
    }
}
