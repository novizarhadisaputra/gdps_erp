<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters\Schemas;

use Filament\Schemas\Schema;

class ProductClusterForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('PC001'),
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Software Development'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
