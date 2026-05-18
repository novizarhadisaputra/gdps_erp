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
            Section::make(__('UOM Information'))
                ->description(__('Define the units of measure used for inventory and service quantification.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Unit Name'))
                        ->required()
                        ->unique(UnitOfMeasure::class, 'name', ignoreRecord: true)
                        ->maxLength(255)
                        ->placeholder(__('e.g. Kilogram, Piece, Hour'))
                        ->helperText(__('The full descriptive name of the measurement unit.')),
                    TextInput::make('code')
                        ->label(__('Unit Code'))
                        ->placeholder(__('Auto-generated'))
                        ->readOnly()
                        ->unique(UnitOfMeasure::class, 'code', ignoreRecord: true)
                        ->helperText(__('A short abbreviation for the unit.')),
                ])->columns(2),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this unit of measure.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Enable or disable this unit for use in the system.')),
                    Toggle::make('is_default')
                        ->label(__('Default Unit'))
                        ->default(false)
                        ->helperText(__('Set as the default unit for new item entries.')),
                ])->columns(2),
        ];
    }
}
