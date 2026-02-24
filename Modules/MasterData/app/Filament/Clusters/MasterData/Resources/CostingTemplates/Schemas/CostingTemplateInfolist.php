<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class CostingTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Template Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Template Name')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('description')
                                    ->label('Description')
                                    ->placeholder('No description provided.')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Items & Costing')
                    ->schema([
                        RepeatableEntry::make('costingTemplateItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Item Name / Description'),
                                TextEntry::make('category')
                                    ->label('Category')
                                    ->badge(),
                                TextEntry::make('quantity')
                                    ->label('Qty')
                                    ->numeric(),
                                TextEntry::make('unit_price')
                                    ->label('Base Price')
                                    ->money('IDR'),
                                TextEntry::make('markup_percent')
                                    ->label('Markup (%)')
                                    ->suffix('%')
                                    ->numeric(),
                                TextEntry::make('unit_price_markup')
                                    ->label('Price (After Markup)')
                                    ->money('IDR'),
                                TextEntry::make('total_price')
                                    ->label('Total Price')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('monthly_cost')
                                    ->label('Monthly Cost')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold)
                                    ->color('primary'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make('Total Monthly Cost')
                    ->schema([
                        TextEntry::make('total_monthly_cost')
                            ->label('Total Monthly Cost Impact')
                            ->state(fn ($record) => $record->getTotalMonthlyCost())
                            ->money('IDR')
                            ->weight(FontWeight::Bold)
                            ->color('primary')
                            ->size(TextSize::Large),
                    ])->columnSpanFull(),
            ]);
    }
}
