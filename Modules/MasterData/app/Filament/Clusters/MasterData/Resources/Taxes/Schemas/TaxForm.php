<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Modules\MasterData\Models\Tax;

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
                ->label('Tax Code')
                ->required()
                ->unique(Tax::class, 'code', ignoreRecord: true)
                ->placeholder('e.g. VAT11, PPN12')
                ->helperText('Unique short code for the tax type.'),
            TextInput::make('name')
                ->label('Tax Name')
                ->required()
                ->maxLength(255)
                ->placeholder('e.g. Value Added Tax 11%')
                ->helperText('Descriptive name of the tax.'),
            Toggle::make('is_active')
                ->label('Active Status')
                ->default(true)
                ->required()
                ->helperText('Enable or disable this tax type globally.'),
        ];
    }
}
