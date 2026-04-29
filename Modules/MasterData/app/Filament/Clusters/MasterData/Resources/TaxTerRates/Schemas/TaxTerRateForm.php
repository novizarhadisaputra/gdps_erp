<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TaxTerRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('TER Category & Rate')
                ->description('Specify the taxpayer category and the corresponding effective tax rate.')
                ->schema([
                    Select::make('category')
                        ->label('Taxpayer Category')
                        ->options([
                            'A' => 'Category A (TK/0, TK/1, K/0)',
                            'B' => 'Category B (TK/2, TK/3, K/1, K/2)',
                            'C' => 'Category C (K/3)',
                        ])
                        ->required()
                        ->native(false)
                        ->helperText('Select the category based on PTKP status.'),
                    TextInput::make('rate')
                        ->label('Tax Rate (%)')
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->prefix('%')
                        ->helperText('Effective tax rate for this range.'),
                ])->columns(2),

            Section::make('Income Range')
                ->description('Define the gross monthly income bracket for this tax rate.')
                ->schema([
                    TextInput::make('min_gross')
                        ->label('Minimum Gross Income')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->helperText('Starting gross income for this bracket.'),
                    TextInput::make('max_gross')
                        ->label('Maximum Gross Income')
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->helperText('Ending gross income for this bracket.'),
                ])->columns(2),

            Section::make('Status')
                ->description('Manage visibility of this rate in calculations.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('If disabled, this rate will be ignored during PPh 21 calculation.'),
                ]),
        ]);
    }
}
