<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PaymentTerms\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\PaymentTerm;

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
            TextInput::make('code')
                ->required()
                ->unique(PaymentTerm::class, 'code', ignoreRecord: true)
                ->placeholder('NET30'),
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->placeholder('Net 30 Days'),
            Toggle::make('is_active')
                ->default(true)
                ->required(),
        ];
    }
}
