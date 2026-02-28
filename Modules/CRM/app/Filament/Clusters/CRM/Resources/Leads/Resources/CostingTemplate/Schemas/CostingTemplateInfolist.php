<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CostingTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General Information')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('name'),
                            TextEntry::make('pic.name')->label('PIC'),
                            TextEntry::make('total_monthly_cost')->money('IDR'),
                        ]),
                    TextEntry::make('description'),
                ]),

            Section::make('Items & Costing')
                ->schema([
                    RepeatableEntry::make('costingTemplateItems')
                        ->schema([
                            Grid::make(4)
                                ->schema([
                                    TextEntry::make('category'),
                                    TextEntry::make('name')->label('Item Name'),
                                    TextEntry::make('quantity'),
                                    TextEntry::make('unit_price')->money('IDR'),
                                ]),
                            Grid::make(3)
                                ->schema([
                                    TextEntry::make('unit_price_markup')->label('Price w/ Markup')->money('IDR'),
                                    TextEntry::make('total_price')->money('IDR'),
                                    TextEntry::make('monthly_cost')->money('IDR'),
                                ]),
                        ])
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
