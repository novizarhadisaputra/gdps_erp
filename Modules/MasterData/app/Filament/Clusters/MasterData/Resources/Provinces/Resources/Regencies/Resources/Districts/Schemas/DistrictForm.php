<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Resources\Districts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('District Details'))
                ->description(__('Link this district to its parent regency and specify official identification details.'))
                ->icon('heroicon-o-home-modern')
                ->columns(2)
                ->schema([
                    Select::make('regency_id')
                        ->relationship('regency', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select regency...'))
                        ->helperText(__('The parent regency for this district.'))
                        ->disabled(fn (?string $operation) => $operation === 'edit' || request()->routeIs('*.regencies.*')),
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder(__('e.g. 3273010'))
                        ->helperText(__('Official regional code for this district.')),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder(__('e.g. Coblong'))
                        ->helperText(__('Identification name of the district.')),
                ]),
        ]);
    }
}
