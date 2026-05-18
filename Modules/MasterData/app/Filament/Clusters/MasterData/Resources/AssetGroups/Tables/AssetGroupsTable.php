<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label(__('Life (Years)'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('rate_straight_line')
                    ->label(__('Rate GL'))
                    ->suffix('%')
                    ->numeric(),
                TextColumn::make('rate_declining_balance')
                    ->label(__('Rate SM'))
                    ->suffix('%')
                    ->numeric(),
                IconColumn::make('is_active')
                    ->label(__('Active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
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
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
