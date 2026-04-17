<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\Tables;

use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Resources\ProfitabilityAnalysisWeekly\Schemas\ProfitabilityAnalysisWeeklyForm;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisWeekliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('week_number')
                    ->label('Week')
                    ->badge()
                    ->sortable(),
                TextColumn::make('achieved_revenue')
                    ->label('Achievement (Real)')
                    ->money('IDR')
                    ->color('success')
                    ->sortable(),
                TextColumn::make('projected_revenue')
                    ->label('Outlook (Forecast)')
                    ->money('IDR')
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Sales Name')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Update Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->schema(fn (Schema $schema) => ProfitabilityAnalysisWeeklyForm::configure($schema)),
                    EditAction::make()
                        ->schema(fn (Schema $schema) => ProfitabilityAnalysisWeeklyForm::configure($schema))
                        ->visible(fn ($record) => $record->monthly->status === ProfitabilityAnalysisMonthlyStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn ($record) => $record->monthly->status === ProfitabilityAnalysisMonthlyStatus::Draft),
                ]),
            ]);
    }
}
