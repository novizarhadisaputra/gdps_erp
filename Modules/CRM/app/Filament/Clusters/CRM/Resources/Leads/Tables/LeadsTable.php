<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Actions\MoveToApproachAction;

class LeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('estimated_amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('probability')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('proposals')
                    ->label('Proposals'),
            ])
            ->filters([
                //
            ])
            ->actions([
                MoveToApproachAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
