<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\UnitOfMeasure;

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
                ->unique(UnitOfMeasure::class, 'name', ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('Kilogram'),
            TextInput::make('code')
                ->required()
                ->unique(UnitOfMeasure::class, 'code', ignoreRecord: true)
                ->maxLength(10)
                ->placeholder('KG'),
        ];
    }
}
