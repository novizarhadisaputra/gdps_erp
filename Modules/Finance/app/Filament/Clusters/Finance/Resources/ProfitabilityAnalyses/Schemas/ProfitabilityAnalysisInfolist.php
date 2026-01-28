<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

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
                                TextEntry::make('revenue_per_month')
                                    ->money('IDR'),
                                TextEntry::make('direct_cost')
                                    ->money('IDR'),
                                TextEntry::make('management_fee')
                                    ->money('IDR'),
                                TextEntry::make('margin_percentage')
                                    ->suffix('%')
                                    ->color(fn (float $state): string => $state < 10 ? 'danger' : ($state < 20 ? 'warning' : 'success')),
                            ]),
                    ]),
                Section::make('Signatures')
                    ->schema([
                        RepeatableEntry::make('signatures')
                            ->label('Digital Signatures')
                            ->schema([
                                TextEntry::make('user_name')->label('Signed By'),
                                TextEntry::make('user_role')->label('Role')->badge(),
                                TextEntry::make('type')->label('Type'),
                                TextEntry::make('signed_at')->dateTime(),
                                TextEntry::make('qr_code')
                                    ->label('QR Verification')
                                    ->html()
                                    ->extraAttributes(['class' => 'w-32 h-32']),
                            ])
                            ->columns(5)
                            ->grid(2),
                    ]),
            ]);
    }
}
