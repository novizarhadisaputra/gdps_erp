<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\MasterData\Models\DirectCostCategory;

class ProfitabilityAnalysisMonthlyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Identitas Periode')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('month')
                                ->options([
                                    'January' => 'January', 'February' => 'February', 'March' => 'March',
                                    'April' => 'April', 'May' => 'May', 'June' => 'June',
                                    'July' => 'July', 'August' => 'August', 'September' => 'September',
                                    'October' => 'October', 'November' => 'November', 'December' => 'December',
                                ])
                                ->required(),
                            TextInput::make('year')
                                ->numeric()
                                ->default(now()->year)
                                ->required(),
                        ]),
                ]),

            Section::make('Monthly Performance Summary')
                ->description('Financial performance snapshot comparing targets, forecasts, and actuals.')
                ->visible(fn (string $operation): bool => $operation !== 'create')

                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('target_revenue')
                                ->label('Budget (RKAP)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->required()
                                ->readOnly()
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->default(fn (Get $get, ?ProfitabilityAnalysisMonthly $record) => $record?->profitabilityAnalysis?->revenue_per_month)
                                ->helperText('Target revenue from Sales Plan (read-only).'),

                            TextInput::make('actual_revenue')
                                ->label('Realized Revenue (Actual)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->readOnly()
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->helperText('Achieved revenue summed from weekly records.'),
                        ]),
                    Grid::make(1)
                        ->schema([
                            TextInput::make('forecast_revenue')
                                ->label('Latest Forecast (RoFo)')
                                ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                ->prefix('IDR ')
                                ->readOnly()
                                ->visible(fn (string $operation): bool => $operation !== 'create')
                                ->helperText('Last expected revenue from weekly updates.'),
                        ])
                        ->visible(fn (string $operation): bool => $operation !== 'create'),
                ]),

            Section::make('Status')
                ->visible(fn (string $operation): bool => $operation !== 'create')
                ->schema([
                    TextInput::make('status')
                        ->readOnly()
                        ->formatStateUsing(fn ($state) => $state instanceof \Filament\Support\Contracts\HasLabel ? $state->getLabel() : $state),
                ]),

            Section::make('Actual Monthly Costs')
                ->description('Provide actual totals for monthly expenses.')
                ->visible(fn (string $operation): bool => $operation !== 'create')
                ->schema([
                    TextInput::make('actual_cost')
                        ->label('Total Actual Cost')
                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                        ->prefix('IDR ')
                        ->readOnly(),

                    Repeater::make('actual_details.manual_costs')
                        ->label('Direct Cost Breakdown')
                        ->itemLabel(fn (array $state): ?string => DirectCostCategory::find($state['direct_cost_category_id'] ?? null)?->name ?? 'New Actual Cost')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    Select::make('direct_cost_category_id')
                                        ->label('Category')
                                        ->options(fn () => DirectCostCategory::where('type', 'direct')->whereNull('parent_id')->pluck('name', 'id'))
                                        ->disabled() // Make it read-only effectively
                                        ->columnSpan(2),
                                    TextInput::make('amount')
                                        ->label('Amount')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->readOnly() // Make it read-only
                                        ->columnSpan(1),
                                ]),
                            TextInput::make('description')
                                ->label('Description/Notes')
                                ->readOnly(),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function calculateTotals(Get $get, Set $set): void
    {
        $directCosts = collect($get('actual_details.manual_costs') ?? [])
            ->sum(fn ($item) => (float) ($item['amount'] ?? 0));

        $set('actual_cost', $directCosts);
    }
}
