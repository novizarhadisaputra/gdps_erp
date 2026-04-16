<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Tables;

use Carbon\Carbon;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfitabilityAnalysisActualsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('month')
                    ->label('Month')
                    ->formatStateUsing(fn (int $state) => Carbon::create()->month($state)->format('F'))
                    ->sortable(),
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('actual_revenue')
                    ->label('Revenue')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('actual_cost')
                    ->label('Costs')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('gross_profit')
                    ->label('Profit')
                    ->state(fn ($record) => $record->actual_revenue - $record->actual_cost)
                    ->money('IDR')
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
