<?php

namespace Modules\MasterData\Filament\Resources\UnitsOfMeasure\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UnitOfMeasureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('Kilogram'),
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(10)
                ->placeholder('KG'),
        ];
    }
}
