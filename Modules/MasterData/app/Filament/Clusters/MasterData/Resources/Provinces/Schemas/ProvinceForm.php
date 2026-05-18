<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Provinces\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProvinceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(__('Province Details'))
                ->description(__('Specify the official code and name for this province.'))
                ->icon('heroicon-o-map')
                ->columns(2)
                ->schema([
                    TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(10)
                        ->placeholder(__('e.g. 32'))
                        ->helperText(__('Standardized regional code (BPS/Kemendagri).')),
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->placeholder(__('e.g. Jawa Barat'))
                        ->helperText(__('Official name of the province.')),
                ]),
        ]);
    }
}
