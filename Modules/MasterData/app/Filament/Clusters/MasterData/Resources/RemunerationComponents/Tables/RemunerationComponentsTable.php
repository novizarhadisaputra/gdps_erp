<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RemunerationComponentsTable
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
                    ->formatStateUsing(fn (string $state): string => str($state)->title()->replace('_', ' '))
                    ->color(fn (string $state): string => match ($state) {
                        'fixed_allowance' => 'success',
                        'non_fixed_allowance' => 'warning',
                        'benefit' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('default_amount')
                    ->money('IDR')
                    ->sortable(),
                IconColumn::make('is_bpjs_base')
                    ->boolean()
                    ->label('BPJS Base'),
                IconColumn::make('is_taxable')
                    ->boolean()
                    ->label('Taxable'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
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
