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
                TextColumn::make('amount_revenue')
                    ->label('Accrue Revenue')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('invoice.number')
                    ->label('Invoice Number')
                    ->placeholder('Belum terbit')
                    ->searchable(),
                TextColumn::make('amount_cost')
                    ->label('Actual Revenue (Amount Cost)')
                    ->money('IDR')
                    ->sortable()
                    ->color(fn ($record) => $record->invoice_id ? 'success' : 'warning')
                    ->weight('bold'),
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
