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
                ->label('Unit Name')
                ->required()
                ->unique(UnitOfMeasure::class, 'name', ignoreRecord: true)
                ->maxLength(255)
                ->placeholder('e.g. Kilogram, Piece, Hour')
                ->helperText('The full descriptive name of the measurement unit.'),
            TextInput::make('code')
                ->label('Unit Code')
                ->required()
                ->unique(UnitOfMeasure::class, 'code', ignoreRecord: true)
                ->maxLength(10)
                ->placeholder('e.g. KG, Pcs, HR')
                ->helperText('A short abbreviation for the unit (max 10 chars).'),
        ];
    }
}
