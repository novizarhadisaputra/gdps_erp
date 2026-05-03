<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxTerRates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TaxTerRatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('Average Effective Tax Rates (TER) for monthly income calculation.')
            ->columns([
                TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('min_gross')
                    ->label('Min Gross Income')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('max_gross')
                    ->label('Max Gross Income')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Rate')
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active Status')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'A' => 'Category A',
                        'B' => 'Category B',
                        'C' => 'Category C',
                    ]),
            ])
            ->defaultSort('category')
            ->defaultSort('min_gross')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ]);
    }
}
