<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;


use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ProfitabilityAnalysisInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('proposal.proposal_number')
                                    ->label('Proposal Number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('workScheme.name')
                                    ->label('Work Scheme'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('tor_document')
                                    ->label('ToR Document')
                                    ->state(fn ($record) => $record->getFirstMedia('tor')?->file_name ?? 'No ToR')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('tor'), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No ToR' ? 'gray' : 'primary'),
                                TextEntry::make('rfp_document')
                                    ->label('RFP Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfp')?->file_name ?? 'No RFP')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('rfp'), true)
                                    ->icon(Heroicon::OutlinedDocumentChartBar)
                                    ->color(fn ($state) => $state === 'No RFP' ? 'gray' : 'primary'),
                                TextEntry::make('rfi_document')
                                    ->label('RFI Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rfi')?->file_name ?? 'No RFI')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('rfi'), true)
                                    ->icon(Heroicon::OutlinedInformationCircle)
                                    ->color(fn ($state) => $state === 'No RFI' ? 'gray' : 'primary'),
                            ]),
                    ]),
                Section::make('Project Parameters')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('productCluster.name')
                                    ->label('Product Cluster'),
                                TextEntry::make('tax.name')
                                    ->label('Tax'),
                                TextEntry::make('projectArea.name')
                                    ->label('Project Area'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'approved' => 'info',
                                        'converted' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ]),
                Section::make('Financials')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('asset_ownership')
                                    ->label('Asset Ownership')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('margin_percentage')
                                    ->label('Gross Margin')
                                    ->suffix('%')
                                    ->color(fn (float $state): string => $state < 10 ? 'danger' : ($state < 20 ? 'warning' : 'success')),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('revenue_per_month')
                                    ->label('Revenue/Mo')
                                    ->money('IDR'),
                                TextEntry::make('direct_cost')
                                    ->label('Direct Cost/Mo')
                                    ->money('IDR'),
                                TextEntry::make('management_fee')
                                    ->label('Mgmt Fee (Flat)')
                                    ->money('IDR'),
                                TextEntry::make('management_expense_rate')
                                    ->label('Mgmt Expense Rate')
                                    ->suffix('%'),
                            ]),
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('ebitda')
                                    ->label('EBITDA')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('ebit')
                                    ->label('EBIT')
                                    ->money('IDR'),
                                TextEntry::make('ebt')
                                    ->label('EBT')
                                    ->money('IDR'),
                                TextEntry::make('net_profit')
                                    ->label('Net Profit')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('success'),
                            ]),
                    ]),
                Section::make('Signatures')
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('signatures')
                            ->view('filament.infolists.digital-signature')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->signatures)),
            ]);
    }
}
