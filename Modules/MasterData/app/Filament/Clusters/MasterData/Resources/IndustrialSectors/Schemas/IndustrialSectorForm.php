<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\IndustrialSectors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\IndustrialSector;

class IndustrialSectorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(IndustrialSector::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            Section::make('Sector Identification')
                ->description('Classify the industrial background of clients or projects.')
                ->schema([
                    TextInput::make('name')
                        ->label('Sector Name')
                        ->placeholder('e.g. Oil & Gas, Aviation, Healthcare')
                        ->required()
                        ->maxLength(255)
                        ->helperText('The descriptive name of the industrial sector.'),
                    TextInput::make('code')
                        ->label('Sector Code')
                        ->placeholder('e.g. SEC-OIL, SEC-AV')
                        ->required()
                        ->unique(IndustrialSector::class, 'code', ignoreRecord: true)
                        ->helperText('Unique short code identifying this sector.'),
                ])->columns(2),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this sector.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive sectors will not appear in selection menus.'),
                    Toggle::make('is_default')
                        ->label('Default Sector')
                        ->default(false)
                        ->helperText('Set as the default sector for new client records.'),
                ])->columns(2),
        ];
    }
}
