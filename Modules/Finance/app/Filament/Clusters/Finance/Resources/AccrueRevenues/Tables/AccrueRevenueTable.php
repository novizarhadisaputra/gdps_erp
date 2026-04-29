<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccrueRevenueTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.number')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('month')
                    ->label('Month')
                    ->formatStateUsing(fn (int $state): string => date('F', mktime(0, 0, 0, $state, 1))),
                TextColumn::make('year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('total_amount_estimated')
                    ->label('Estimated Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total Estimated')),
                TextColumn::make('total_amount_actual')
                    ->label('Actual Revenue')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('Total Actual'))
                    ->weight('bold')
                    ->color(fn ($record) => $record->total_amount_actual > 0 ? 'success' : 'gray'),
                TextColumn::make('description')
                    ->label('Notes')
                    ->limit(20),
            ])
            ->defaultSort('year', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ]);
    }
}
