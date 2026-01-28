<?php

namespace Modules\MasterData\Filament\Resources\Taxes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->placeholder('VAT11'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('VAT 11%'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
