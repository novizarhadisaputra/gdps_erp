<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApprovalRules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ApprovalRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('resource_type')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->label('Resource'),
                TextColumn::make('criteria_field'),
                TextColumn::make('operator'),
                TextColumn::make('value')
                    ->numeric(),
                TextColumn::make('approver_role')
                    ->badge(),
                TextColumn::make('signature_type'),
                TextColumn::make('order')
                    ->sortable(),
                ToggleColumn::make('is_active'),
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
