<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters\Schemas;

use Filament\Schemas\Schema;

class ProductClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make()
                    ->schema([
                        \Filament\Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true),
                        \Filament\Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        \Filament\Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }
}
