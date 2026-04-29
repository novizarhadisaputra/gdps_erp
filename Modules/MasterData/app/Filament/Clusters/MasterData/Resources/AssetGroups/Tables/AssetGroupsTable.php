<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssetGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('useful_life_years')
                    ->label('Life (Years)')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rate_straight_line')
                    ->label('Rate GL')
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('rate_declining_balance')
                    ->label('Rate SM')
                    ->suffix('%')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(null)
                    ->color('warning')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
