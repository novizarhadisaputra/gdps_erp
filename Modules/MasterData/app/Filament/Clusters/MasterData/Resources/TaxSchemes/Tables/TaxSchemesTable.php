<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\TaxSchemes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;

class TaxSchemesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('scheme_code')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('rate_percentage')->searchable()->sortable(),
                \Filament\Tables\Columns\TextColumn::make('notes')->limit(50),
                \Filament\Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
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
