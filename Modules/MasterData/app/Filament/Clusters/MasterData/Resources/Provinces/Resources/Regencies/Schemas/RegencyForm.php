<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Resources\Regencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Regency Details'))
                ->description(__('Link this regency to its parent province and specify official identification details.'))
                ->icon('heroicon-o-building-office-2')
                ->columns(2)
                ->schema([
                    Select::make('province_id')
                        ->relationship('province', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->placeholder(__('Select province...'))
                        ->helperText(__('The parent province for this regency.'))
                        ->disabled(fn (?string $operation) => $operation === 'edit' || request()->routeIs('*.provinces.*')),
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder(__('e.g. 3273'))
                        ->helperText(__('Official regional code for this regency.')),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->placeholder(__('e.g. Kota Bandung'))
                        ->helperText(__('Identification name (City or Regency).')),
                ]),
        ]);
    }
}
