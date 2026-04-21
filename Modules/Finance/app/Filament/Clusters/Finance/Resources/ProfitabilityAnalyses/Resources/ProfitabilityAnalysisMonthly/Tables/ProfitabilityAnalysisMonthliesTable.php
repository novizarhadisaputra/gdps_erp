<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use App\Services\AnalyticsCacheService;

class ProfitabilityAnalysisMonthliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->label('Month')
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('target_revenue')
                    ->label('Target RoFo')
                    ->money('IDR')
                    ->sortable()
                    ->tooltip('Monthly target baseline automatically pulled from Sales Plan.')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('forecast_revenue')
                    ->label('Latest RoFo')
                    ->money('IDR')
                    ->sortable()
                    ->tooltip('The most recent Rolling Forecast (RoFo) figure.')
                    ->color('warning'),
                TextColumn::make('actual_revenue')
                    ->label('Actual')
                    ->money('IDR')
                    ->sortable()
                    ->tooltip('Realized revenue figure as confirmed by Finance.')
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->tooltip('Current status of the monthly profitability analysis.'),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === ProfitabilityAnalysisMonthlyStatus::Draft),

                    Action::make('updatePerformance')
                        ->label('Update Performance')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('forecast_revenue')
                                        ->label('Latest RoFo')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->required()
                                        ->live()
                                        ->afterStateUpdated(function ($state, $record, Set $set) {
                                            $oldValue = (float) $record->forecast_revenue;
                                            $newValue = (float) str_replace(['.', ','], ['', '.'], $state);
                                            $set('forecast_delta', $newValue - $oldValue);
                                        }),
                                    TextInput::make('forecast_delta')
                                        ->label('RoFo Delta')
                                        ->readOnly()
                                        ->prefix('IDR ')
                                        ->extraInputAttributes(fn ($state) => [
                                            'class' => (float) $state >= 0 ? 'text-success-600 dark:text-success-400 font-bold' : 'text-danger-600 dark:text-danger-400 font-bold',
                                        ])
                                        ->dehydrated(false),
                                    
                                    TextInput::make('actual_revenue')
                                        ->label('Actual Revenue')
                                        ->currencyMask(thousandSeparator: '.', decimalSeparator: ',', precision: 0)
                                        ->prefix('IDR ')
                                        ->disabled(fn () => !auth()->user()->can('UpdateActual:Finance')) // Authorization check
                                        ->live()
                                        ->afterStateUpdated(function ($state, $record, Set $set) {
                                            $oldValue = (float) $record->actual_revenue;
                                            $newValue = (float) str_replace(['.', ','], ['', '.'], $state);
                                            $set('actual_delta', $newValue - $oldValue);
                                        }),
                                    TextInput::make('actual_delta')
                                        ->label('Actual Delta')
                                        ->readOnly()
                                        ->prefix('IDR ')
                                        ->extraInputAttributes(fn ($state) => [
                                            'class' => (float) $state >= 0 ? 'text-success-600 dark:text-success-400 font-bold' : 'text-danger-600 dark:text-danger-400 font-bold',
                                        ])
                                        ->dehydrated(false),
                                ]),
                        ])
                        ->mountUsing(fn (Schema $form, ProfitabilityAnalysisMonthly $record) => $form->fill([
                            'forecast_revenue' => $record->forecast_revenue,
                            'actual_revenue' => $record->actual_revenue,
                            'forecast_delta' => 0,
                            'actual_delta' => 0,
                        ]))
                        ->action(function (array $data, ProfitabilityAnalysisMonthly $record): void {
                            $oldForecast = (float) $record->forecast_revenue;
                            $newForecast = (float) $data['forecast_revenue'];
                            
                            $oldActual = (float) $record->actual_revenue;
                            $newActual = (float) ($data['actual_revenue'] ?? $oldActual);

                            // Update the record
                            $record->update([
                                'forecast_revenue' => $newForecast,
                                'actual_revenue' => $newActual,
                            ]);

                            // Invalidate analytics caches
                            $cache = app(AnalyticsCacheService::class);
                            $cache->forget('crm.stats_overview');
                            $cache->forget('crm.sales_performance_cumulative');
                            $cache->forget('crm.lead_pipeline_levels');
                            $cache->forget('crm.team_performance');
                        })
                        ->visible(fn ($record) => $record->status === ProfitabilityAnalysisMonthlyStatus::Draft),
                    
                    Action::make('finalize')
                        ->label('Finalize Performance')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === ProfitabilityAnalysisMonthlyStatus::Draft)
                        ->action(fn ($record) => $record->update(['status' => ProfitabilityAnalysisMonthlyStatus::Finalized])),

                    Action::make('reopen')
                        ->label('Re-open for Edit')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === ProfitabilityAnalysisMonthlyStatus::Finalized)
                        ->action(fn ($record) => $record->update(['status' => ProfitabilityAnalysisMonthlyStatus::Draft])),

                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === ProfitabilityAnalysisMonthlyStatus::Draft),
                ]),
            ]);
    }
}
