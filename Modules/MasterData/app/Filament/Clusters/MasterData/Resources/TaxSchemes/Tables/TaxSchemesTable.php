<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxSchemesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('scheme_code')->searchable()->sortable(),
                TextColumn::make('rate_percentage')->searchable()->sortable(),
                TextColumn::make('notes')->limit(50),
                IconColumn::make('is_active')->boolean()->label('Active'),
                IconColumn::make('is_default')->boolean()->label('Default'),
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
