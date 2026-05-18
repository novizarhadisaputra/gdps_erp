<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Resources\Villages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class VillageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Village Details'))
                ->description(__('Link this village or ward to its parent district and specify official identification details.'))
                ->icon('heroicon-o-home')
                ->columns(2)
                ->schema([
                    Select::make('district_id')
                        ->relationship('district', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select district...'))
                        ->helperText(__('The parent district for this village.'))
                        ->disabled(fn (?string $operation) => $operation === 'edit' || request()->routeIs('*.districts.*')),
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder(__('e.g. 3273010001'))
                        ->helperText(__('Official regional code for this village.')),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder(__('e.g. Lebak Siliwangi'))
                        ->helperText(__('Official name of the village/ward.')),
                ]),
        ]);
    }
}
