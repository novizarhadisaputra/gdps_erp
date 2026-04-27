<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AccrueRevenueInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Project Information')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('project.name')
                                ->label('Project'),
                            TextEntry::make('month')
                                ->formatStateUsing(fn (int $state): string => date('F', mktime(0, 0, 0, $state, 1))),
                            TextEntry::make('year'),
                        ]),
                ]),

            Section::make('Revenue Details')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('amount_revenue')
                                ->label('Accrued Revenue')
                                ->money('IDR'),
                            TextEntry::make('amount_cost')
                                ->label('Actual Amount (Invoice)')
                                ->money('IDR'),
                        ]),
                    TextEntry::make('invoice.number')
                        ->label('Invoice Reference')
                        ->placeholder('No Invoice Linked'),
                    TextEntry::make('description')
                        ->markdown()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
