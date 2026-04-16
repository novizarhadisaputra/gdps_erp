<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Models\DirectCostCategory;

class ProfitabilityAnalysisActualForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Monthly Actual Revenue')
                ->description('Revenue is automatically calculated from your weekly projections snapshot.')
                ->schema([
                    TextInput::make('actual_revenue')
                        ->label('Realized Revenue')
                        ->numeric()
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR ')
                        ->readOnly()
                        ->helperText('This value is automatically calculated based on the LATEST weekly projection.'),
                ]),

            Section::make('Monthly Actual Costs')
                ->description('Provide actual totals for monthly direct cost categories.')
                ->schema([
                    TextInput::make('actual_cost')
                        ->label('Total Actual Cost')
                        ->numeric()
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR ')
                        ->readOnly()
                        ->helperText('Automatically calculated from the breakdown below.'),

                    Repeater::make('actual_details.manual_costs')
                        ->label('Direct Cost Breakdown')
                        ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'] ?? null)?->name ?? 'New Actual Cost')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('direct_cost_category_id')
                                        ->label('Category')
                                        ->options(fn () => DirectCostCategory::where('type', 'direct')->whereNull('parent_id')->pluck('name', 'id'))
                                        ->required()
                                        ->distinct()
                                        ->live()
                                        ->createOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                        ->columnSpan(2),
                                    TextInput::make('amount')
                                        ->label('Amount')
                                        ->numeric()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set))
                                        ->columnSpan(1),
                                ]),
                            TextInput::make('description')
                                ->label('Description/Notes'),

                            Repeater::make('sub_items')
                                ->label('Sub-component Breakdown')
                                ->schema([
                                    Grid::make(3)
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Descr')
                                                ->required()
                                                ->columnSpan(2),
                                            TextInput::make('amount')
                                                ->label('Cost')
                                                ->numeric()
                                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                                ->prefix('IDR ')
                                                ->required()
                                                ->columnSpan(1),
                                        ]),
                                ])
                                ->collapsible()
                                ->defaultItems(0),
                        ])
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set)),
                ]),

            Section::make('Indirect Costs (Overhead)')
                ->description('Actual overhead expenses and miscellaneous fees.')
                ->schema([
                    Repeater::make('actual_details.indirect_costs')
                        ->label('Indirect Items')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('direct_cost_category_id')
                                        ->label('Category')
                                        ->options(fn () => DirectCostCategory::where('type', 'indirect')->whereNull('parent_id')->pluck('name', 'id'))
                                        ->required()
                                        ->columnSpan(2),
                                    TextInput::make('amount')
                                        ->label('Amount')
                                        ->numeric()
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set))
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set)),
                ]),
        ]);
    }

    public static function calculateTotals(Get $get, Set $set): void
    {
        $directCosts = collect($get('actual_details.manual_costs') ?? [])
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        $indirectCosts = collect($get('actual_details.indirect_costs') ?? [])
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        $set('actual_cost', $directCosts + $indirectCosts);
    }
}
