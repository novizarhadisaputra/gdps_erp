<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RevenueTypes\Schemas\RevenueTypeForm;

class RevenueTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Type Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->label(__('Type Code'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('Active Status'))
                    ->boolean(),
                IconColumn::make('is_default')
                    ->label(__('Default'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
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
                    EditAction::make()
                        ->schema(fn (Schema $schema) => RevenueTypeForm::configure($schema)),
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
