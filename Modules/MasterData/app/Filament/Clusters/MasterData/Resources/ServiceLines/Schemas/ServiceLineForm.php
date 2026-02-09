<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ServiceLines\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\ServiceLine;

class ServiceLineForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->model(ServiceLine::class)
            ->components(static::schema());
    }

    public static function schema(): array
    {
        return [
            TextInput::make('code')
                ->required()
                ->unique(ServiceLine::class, 'code', ignoreRecord: true)
                ->placeholder('SL001'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Labor Supply'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
