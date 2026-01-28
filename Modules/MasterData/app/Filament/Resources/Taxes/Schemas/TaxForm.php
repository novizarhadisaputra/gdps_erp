<?php

namespace Modules\MasterData\Filament\Resources\Taxes\Schemas;

use Filament\Schemas\Schema;

class TaxForm
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
                ->placeholder('VAT11'),
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('VAT 11%'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
