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
                    ->label('Target (RKAP)')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('forecast_revenue')
                    ->label('RoFo')
                    ->money('IDR')
                    ->sortable()
                    ->color('warning'),
                TextColumn::make('actual_revenue')
                    ->label('Actual')
                    ->money('IDR')
                    ->sortable()
                    ->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
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
