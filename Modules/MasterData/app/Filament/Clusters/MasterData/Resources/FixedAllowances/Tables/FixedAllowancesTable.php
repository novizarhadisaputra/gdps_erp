<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\FixedAllowances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class FixedAllowancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                IconColumn::make('is_bpjs_base')->boolean()->label('BPJS Base')->sortable(),
                IconColumn::make('is_taxable')->boolean()->label('Taxable')->sortable(),
                TextColumn::make('default_amount')->money('IDR')->sortable(),
                IconColumn::make('is_active')->boolean()->label('Active'),
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
