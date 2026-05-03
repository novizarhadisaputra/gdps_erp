<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfitabilityAnalysisRevisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('PA Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sequence_number')
                    ->label('Rev #')
                    ->formatStateUsing(fn ($state) => sprintf('REV/%02d', $state))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Revision Reason')
                    ->wrap()
                    ->limit(100),
                TextColumn::make('user.name')
                    ->label('Revised By')
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ]);
    }
}
