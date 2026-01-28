<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('PC001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Software Development'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
