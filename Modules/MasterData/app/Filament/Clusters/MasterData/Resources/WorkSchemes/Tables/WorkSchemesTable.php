<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Tables;

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
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkSchemes\Schemas\WorkSchemeForm;

class WorkSchemesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label(__('Active')),
                IconColumn::make('is_default')
                    ->boolean()
                    ->label(__('Default')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->schema(fn (Schema $schema) => WorkSchemeForm::configure($schema)),
                    EditAction::make()
                        ->schema(fn (Schema $schema) => WorkSchemeForm::configure($schema)),
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
