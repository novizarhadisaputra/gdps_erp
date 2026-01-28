<?php

namespace Modules\MasterData\Filament\Resources\ProjectAreas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            TextInput::make('code')
                ->unique(ignoreRecord: true)
                ->nullable()
                ->placeholder('PA001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Jakarta Area'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
