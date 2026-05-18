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
            Section::make(__('TER Category & Rate'))
                ->description(__('Specify the taxpayer category and the corresponding effective tax rate.'))
                ->schema([
                    Select::make('category')
                        ->label(__('Taxpayer Category'))
                        ->options([
                            'A' => __('Category A (TK/0, TK/1, K/0)'),
                            'B' => __('Category B (TK/2, TK/3, K/1, K/2)'),
                            'C' => __('Category C (K/3)'),
                        ])
                        ->required()
                        ->native(false)
                        ->helperText(__('Select the category based on PTKP status.')),
                    TextInput::make('rate')
                        ->label(__('Tax Rate (%)'))
                        ->numeric()
                        ->step(0.01)
                        ->required()
                        ->prefix('%')
                        ->helperText(__('Effective tax rate for this range.')),
                ])->columns(2),

            Section::make(__('Income Range'))
                ->description(__('Define the gross monthly income bracket for this tax rate.'))
                ->schema([
                    TextInput::make('min_gross')
                        ->label(__('Minimum Gross Income'))
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->helperText(__('Starting gross income for this bracket.')),
                    TextInput::make('max_gross')
                        ->label(__('Maximum Gross Income'))
                        ->numeric()
                        ->prefix('Rp')
                        ->required()
                        ->helperText(__('Ending gross income for this bracket.')),
                ])->columns(2),

            Section::make(__('Status'))
                ->description(__('Manage visibility of this rate in calculations.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('If disabled, this rate will be ignored during PPh 21 calculation.')),
                ]),
        ]);
    }
}
