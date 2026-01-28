<?php

namespace Modules\MasterData\Filament\Resources\ProjectAreas\Schemas;

use Filament\Schemas\Schema;

class ProjectAreaForm
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
                ->unique(ignoreRecord: true)
                ->nullable()
                ->placeholder('PA001'),
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Jakarta Area'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
