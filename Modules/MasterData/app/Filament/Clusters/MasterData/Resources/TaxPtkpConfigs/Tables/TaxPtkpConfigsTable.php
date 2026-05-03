<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxPtkpConfigs\Tables;

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

class TaxPtkpConfigsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->description('List of Non-Taxable Income (PTKP) limits used for calculating PPh 21.')
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax_category')
                    ->label('Category')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('annual_amount')
                    ->label('Annual Amount')
                    ->money('IDR')
                    ->sortable(),
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
