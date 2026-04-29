<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
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
            Section::make('UOM Information')
                ->description('Define the units of measure used for inventory and service quantification.')
                ->schema([
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
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this unit of measure.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Enable or disable this unit for use in the system.'),
                    Toggle::make('is_default')
                        ->label('Default Unit')
                        ->default(false)
                        ->helperText('Set as the default unit for new item entries.'),
                ])->columns(2),
        ];
    }
}
