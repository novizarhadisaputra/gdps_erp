<?php

namespace Modules\MasterData\Filament\Resources\ProjectTypes\Schemas;

use Filament\Schemas\Schema;

class ProjectTypeForm
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
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('INTERNAL'),
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Internal Project'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
