<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class CostingTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('costing_template_items_count')
                    ->counts('costingTemplateItems')
                    ->label('Items'),
                TextColumn::make('total_amount') // Need to aggregate specific template totals?
                    ->label('Total Investment')
                    ->money('IDR')
                    ->hidden(), // Calculated field might be tricky on parent if not persisted.
                    // For now, maybe just show name and items count.
                TextColumn::make('created_at')
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
