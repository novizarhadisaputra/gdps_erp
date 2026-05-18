<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxPtkpConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('PTKP Configuration'))
                    ->description(__('Define Non-Taxable Income (PTKP) limits for individual taxpayers.'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('PTKP Code'))
                            ->placeholder(__('e.g., K/0, K/1, TK/0'))
                            ->helperText(__('Standard PTKP status code.'))
                            ->required(),
                        TextInput::make('name')
                            ->label(__('Description'))
                            ->placeholder(__('e.g., Kawin 0 Tanggungan'))
                            ->helperText(__('Full description of the PTKP status.'))
                            ->required(),
                        TextInput::make('tax_category')
                            ->label(__('TER Category'))
                            ->placeholder(__('e.g., A, B, C'))
                            ->helperText(__('Category for Average Tax Rate (TER) calculation.'))
                            ->required(),
                        TextInput::make('annual_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->label(__('Annual PTKP Amount'))
                            ->placeholder(__('e.g., 54000000'))
                            ->helperText(__('The non-taxable amount per year.'))
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label(__('Active Status'))
                            ->helperText(__('Toggle to enable or disable this PTKP status.')),
                        Toggle::make('is_default')
                            ->label(__('Set as Default'))
                            ->default(false)
                            ->helperText(__('If enabled, this will be the default PTKP configuration.')),
                    ])->columns(2),
            ]);
    }
}
