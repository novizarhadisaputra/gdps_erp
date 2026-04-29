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
                Section::make('PTKP Configuration')
                    ->description('Define Non-Taxable Income (PTKP) limits for individual taxpayers.')
                    ->schema([
                        TextInput::make('code')
                            ->label('PTKP Code')
                            ->placeholder('e.g., K/0, K/1, TK/0')
                            ->helperText('Standard PTKP status code.')
                            ->required(),
                        TextInput::make('name')
                            ->label('Description')
                            ->placeholder('e.g., Kawin 0 Tanggungan')
                            ->helperText('Full description of the PTKP status.')
                            ->required(),
                        TextInput::make('tax_category')
                            ->label('TER Category')
                            ->placeholder('e.g., A, B, C')
                            ->helperText('Category for Average Tax Rate (TER) calculation.')
                            ->required(),
                        TextInput::make('annual_amount')
                            ->numeric()
                            ->prefix('Rp')
                            ->label('Annual PTKP Amount')
                            ->placeholder('e.g., 54000000')
                            ->helperText('The non-taxable amount per year.')
                            ->required(),
                        Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Status')
                            ->helperText('Toggle to enable or disable this PTKP status.'),
                        Toggle::make('is_default')
                            ->label('Set as Default')
                            ->default(false)
                            ->helperText('If enabled, this will be the default PTKP configuration.'),
                    ])->columns(2),
            ]);
    }
}
