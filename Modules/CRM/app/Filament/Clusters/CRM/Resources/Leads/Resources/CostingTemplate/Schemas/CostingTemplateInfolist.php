<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class CostingTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General Information')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('code')
                                ->label('Template Code')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('name')
                                ->label('Template Name'),
                            TextEntry::make('pic.name')
                                ->label('PIC'),
                        ]),
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('total_monthly_cost')
                                ->label('Monthly Total')
                                ->money('IDR')
                                ->color('success')
                                ->weight(FontWeight::Bold),
                            TextEntry::make('total_amount')
                                ->label('Overall Total')
                                ->money('IDR')
                                ->weight(FontWeight::Bold),
                        ]),
                    TextEntry::make('description')
                        ->placeholder('No description provided.'),
                ])->columnSpanFull(),

            Section::make('Items & Costing Breakdown')
                ->description('List of operational items extracted from the COGS document.')
                ->schema([
                    RepeatableEntry::make('costingTemplateItems')
                        ->label(false)
                        ->schema([
                            Grid::make(6)
                                ->schema([
                                    TextEntry::make('category')
                                        ->badge(),
                                    TextEntry::make('name')
                                        ->label('Item Name')
                                        ->columnSpan(2)
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make('quantity')
                                        ->numeric(),
                                    TextEntry::make('unit')
                                        ->label('UOM'),
                                    TextEntry::make('total_price')
                                        ->label('Sub-Total Cost')
                                        ->money('IDR')
                                        ->weight(FontWeight::Bold)
                                        ->color('primary'),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('unit_price')
                                        ->label('Unit Price')
                                        ->money('IDR'),
                                    TextEntry::make('markup_percent')
                                        ->label('Markup (%)')
                                        ->suffix('%'),
                                    TextEntry::make('monthly_cost')
                                        ->label('Monthly Impact')
                                        ->money('IDR'),
                                ])
                                ->visible(fn ($record) => $record?->unit_price > 0),
                        ])
                        ->columnSpanFull()
                        ->grid(1), // Force single column for better readability if needed
                ])->columnSpanFull(),
        ]);
    }
}
