<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPasal17Rates\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxPasal17RatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('min_amount')
                    ->label('Min Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('max_amount')
                    ->label('Max Amount')
                    ->money('IDR')
                    ->placeholder('Unlimited')
                    ->sortable(),
                TextColumn::make('rate')
                    ->label('Tax Rate')
                    ->formatStateUsing(fn ($state) => $state.'%')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->defaultSort('min_amount')
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
