<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\IndustrialSector;

class IndustrialSectorForm
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
                ->unique(IndustrialSector::class, 'code', ignoreRecord: true)
                ->placeholder('IS001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Oil & Gas'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
