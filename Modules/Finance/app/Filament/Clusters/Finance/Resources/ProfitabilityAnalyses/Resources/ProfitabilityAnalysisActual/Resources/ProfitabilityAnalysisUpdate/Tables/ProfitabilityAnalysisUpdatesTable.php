<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class ProfitabilityAnalysisUpdatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('week_number')
                    ->label('Week')
                    ->sortable(),
                TextColumn::make('year')
                    ->sortable(),
                TextColumn::make('projected_revenue')
                    ->label('Projected Revenue')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Updated By')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label('Notes')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('created_at')
                    ->label('Update Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
