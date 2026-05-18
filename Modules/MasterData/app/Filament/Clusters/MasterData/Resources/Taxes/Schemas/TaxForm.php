<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
            Section::make(__('Tax Definition'))
                ->description(__('Define standard tax types and codes used in financial calculations.'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('Tax Name'))
                        ->placeholder(__('e.g. Value Added Tax 11%, Income Tax'))
                        ->required()
                        ->maxLength(255)
                        ->helperText(__('Full descriptive name of the tax.')),
                    TextInput::make('code')
                        ->label(__('Tax Code'))
                        ->placeholder(__('e.g. VAT-11, PPN-12'))
                        ->required()
                        ->unique(Tax::class, 'code', ignoreRecord: true)
                        ->helperText(__('Unique short identifier for the tax type.')),
                    TextInput::make('rate')
                        ->label(__('Tax Rate'))
                        ->numeric()
                        ->suffix('%')
                        ->default(0)
                        ->required()
                        ->helperText(__('The official percentage rate for this tax (e.g., 12 for 12%).')),
                    Select::make('category')
                        ->options([
                            'sales' => __('Sales (Output VAT)'),
                            'purchase' => __('Purchase (Input VAT)'),
                            'internal' => __('Internal / PA Estimations'),
                        ])
                        ->required()
                        ->default('sales')
                        ->placeholder(__('Select tax category'))
                        ->helperText(__('Defines if the tax is applied to sales (Output), purchases (Input), or internal estimations.'))
                        ->native(false),
                    Select::make('calculation_type')
                        ->options([
                            'exclusive' => __('Exclusive (Standard: DPP x Rate)'),
                            'inclusive' => __('Inclusive (Gross Up: Tax inside Price)'),
                            'formula' => __('Nilai Lain (CoreTax: Adj. Ratio x DPP x Rate)'),
                        ])
                        ->required()
                        ->default('exclusive')
                        ->placeholder(__('Select calculation method'))
                        ->helperText(__('Determines how the tax amount is derived from the base price (DPP).'))
                        ->native(false)
                        ->live(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('base_rate_numerator')
                                ->label(__('DPP Numerator'))
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->placeholder(__('1'))
                                ->helperText(__('The multiplier for Custom Formula calculation.')),
                            TextInput::make('base_rate_denominator')
                                ->label(__('DPP Denominator'))
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->placeholder(__('1'))
                                ->helperText(__('The divisor for Custom Formula calculation.')),
                        ])
                        ->visible(fn ($get) => $get('calculation_type') === 'formula'),
                ])->columns(3),

            Section::make(__('Status & Defaults'))
                ->description(__('Manage the availability and default status of this tax.'))
                ->schema([
                    Toggle::make('is_active')
                        ->label(__('Active Status'))
                        ->default(true)
                        ->helperText(__('Inactive taxes will not be available for selection in new transactions.')),
                    Toggle::make('is_default')
                        ->label(__('Default Tax'))
                        ->default(false)
                        ->helperText(__('Sets this as the default tax type for standard items.')),
                ])->columns(2),
        ];
    }
}
