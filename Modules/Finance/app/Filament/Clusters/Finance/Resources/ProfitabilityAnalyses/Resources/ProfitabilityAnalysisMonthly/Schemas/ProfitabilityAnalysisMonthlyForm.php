<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Schemas\DirectCostCategoryForm;
use Modules\MasterData\Models\DirectCostCategory;

class ProfitabilityAnalysisMonthlyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Monthly Performance Summary')
                ->description('Financial performance snapshot comparing targets, forecasts, and actuals.')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('target_revenue')
                                ->label('Budget (RKAP)')
                                ->numeric()
                                ->prefix('IDR ')
                                ->readOnly()
                                ->helperText('Initial revenue target from PA.'),

                            TextInput::make('forecast_revenue')
                                ->label('Rolling Forecast (RoFo)')
                                ->numeric()
                                ->prefix('IDR ')
                                ->readOnly()
                                ->helperText('Last expected revenue from weekly updates.'),

                            TextInput::make('actual_revenue')
                                ->label('Realized Revenue (Actual)')
                                ->numeric()
                                ->prefix('IDR ')
                                ->readOnly()
                                ->helperText('Achieved revenue summed from weekly records.'),
                        ]),
                ]),

            Section::make('Status & Timeframe')
                ->schema([
                    Grid::make(3)
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
                            Select::make('status')
                                ->options([
                                    'draft' => 'Draft',
                                    'finalized' => 'Finalized',
                                ])
                                ->default('draft')
                                ->required(),
                        ]),
                ]),

            Section::make('Actual Monthly Costs')
                ->description('Provide actual totals for monthly expenses.')
                ->schema([
                    TextInput::make('actual_cost')
                        ->label('Total Actual Cost')
                        ->numeric()
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
                                        ->required()
                                        ->distinct()
                                        ->live()
                                        ->createOptionForm(DirectCostCategoryForm::schema(type: 'direct'))
                                        ->columnSpan(2),
                                    TextInput::make('amount')
                                        ->label('Amount')
                                        ->numeric()
                                        ->prefix('IDR ')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set))
                                        ->columnSpan(1),
                                ]),
                            TextInput::make('description')
                                ->label('Description/Notes'),
                        ])
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(fn (Get $get, Set $set) => self::calculateTotals($get, $set)),
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
