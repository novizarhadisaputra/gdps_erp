<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ContractTypes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContractTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_active')
                    ->label('Active Status')
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->icon(fn ($state) => $state ? 'heroicon-o-star' : null)
                    ->color('warning'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
