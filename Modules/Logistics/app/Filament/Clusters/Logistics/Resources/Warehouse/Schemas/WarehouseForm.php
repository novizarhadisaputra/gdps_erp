<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class WarehouseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Warehouse Details')
                    ->description('Manage warehouse information and operational status.')
                    ->aside()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Warehouse Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon(Heroicon::OutlinedHome)
                                    ->placeholder('e.g., Main Distribution Center')
                                    ->helperText('The primary name used to identify this facility.'),

                                TextInput::make('code')
                                    ->label('Unique Code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->prefixIcon(Heroicon::OutlinedHashtag)
                                    ->placeholder('e.g., WH-MAIN-01')
                                    ->helperText('A unique identifier used for internal logistics tracking.'),

                                Textarea::make('address')
                                    ->label('Physical Address')
                                    ->rows(3)
                                    ->placeholder('Enter the full address of the warehouse...')
                                    ->helperText('Include street, city, and zip code.')
                                    ->columnSpanFull(),

                                Toggle::make('is_active')
                                    ->label('Operational Status')
                                    ->default(true)
                                    ->onIcon(Heroicon::OutlinedCheck)
                                    ->offIcon(Heroicon::OutlinedXMark)
                                    ->helperText('Disable this to prevent further stock movements to/from this warehouse.'),
                            ]),
                    ]),
            ]);
    }
}
