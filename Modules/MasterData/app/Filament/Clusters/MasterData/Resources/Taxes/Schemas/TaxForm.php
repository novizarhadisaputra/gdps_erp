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
            Section::make('Tax Definition')
                ->description('Define standard tax types and codes used in financial calculations.')
                ->schema([
                    TextInput::make('name')
                        ->label('Tax Name')
                        ->placeholder('e.g. Value Added Tax 11%, Income Tax')
                        ->required()
                        ->maxLength(255)
                        ->helperText('Full descriptive name of the tax.'),
                    TextInput::make('code')
                        ->label('Tax Code')
                        ->placeholder('e.g. VAT-11, PPN-12')
                        ->required()
                        ->unique(Tax::class, 'code', ignoreRecord: true)
                        ->helperText('Unique short identifier for the tax type.'),
                    TextInput::make('rate')
                        ->label('Tax Rate')
                        ->numeric()
                        ->suffix('%')
                        ->default(0)
                        ->required()
                        ->helperText('The official percentage rate for this tax (e.g., 12 for 12%).'),
                    Select::make('category')
                        ->options([
                            'sales' => 'Sales (Output VAT)',
                            'purchase' => 'Purchase (Input VAT)',
                            'internal' => 'Internal / PA Estimations',
                        ])
                        ->required()
                        ->default('sales'),
                    Select::make('calculation_type')
                        ->options([
                            'exclusive' => 'Exclusive (DPP x Rate)',
                            'inclusive' => 'Inclusive (Gross Up)',
                            'formula' => 'Formula (Custom Ratio)',
                        ])
                        ->required()
                        ->default('exclusive')
                        ->live(),
                    Grid::make(2)
                        ->schema([
                            TextInput::make('base_rate_numerator')
                                ->label('DPP Numerator')
                                ->numeric()
                                ->default(1)
                                ->required(),
                            TextInput::make('base_rate_denominator')
                                ->label('DPP Denominator')
                                ->numeric()
                                ->default(1)
                                ->required(),
                        ])
                        ->visible(fn ($get) => $get('calculation_type') === 'formula'),
                ])->columns(3),

            Section::make('Status & Defaults')
                ->description('Manage the availability and default status of this tax.')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Active Status')
                        ->default(true)
                        ->helperText('Inactive taxes will not be available for selection in new transactions.'),
                    Toggle::make('is_default')
                        ->label('Default Tax')
                        ->default(false)
                        ->helperText('Sets this as the default tax type for standard items.'),
                ])->columns(2),
        ];
    }
}
