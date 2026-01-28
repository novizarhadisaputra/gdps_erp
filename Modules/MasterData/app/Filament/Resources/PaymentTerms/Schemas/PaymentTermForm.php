<?php

namespace Modules\MasterData\Filament\Resources\PaymentTerms\Schemas;

use Filament\Schemas\Schema;

class PaymentTermForm
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
                ->placeholder('NET30'),
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Net 30 Days'),
            \Filament\Forms\Components\Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
