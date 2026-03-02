<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class CostingTemplateItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Item Name')
                    ->description(fn ($record) => $record->item?->code ?? '')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->alignment(Alignment::End)
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit')
                    ->label('UOM')
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label('Base Price')
                    ->money('IDR')
                    ->alignment(Alignment::End)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('markup_percent')
                    ->label('Markup')
                    ->suffix('%')
                    ->color('info')
                    ->alignment(Alignment::End)
                    ->toggleable()
                    ->sortable(),
                TextColumn::make('unit_price_markup')
                    ->label('Price w/ Markup')
                    ->money('IDR')
                    ->alignment(Alignment::End)
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Total Cost')
                    ->money('IDR')
                    ->weight('bold')
                    ->alignment(Alignment::End)
                    ->summarize(Sum::make()->label('Total')->money('IDR'))
                    ->sortable(),
                TextColumn::make('monthly_cost')
                    ->label('Monthly Cost')
                    ->money('IDR')
                    ->weight('bold')
                    ->alignment(Alignment::End)
                    ->summarize(Sum::make()->label('Total Monthly')->money('IDR'))
                    ->sortable(),
                TextColumn::make('depreciation_months')
                    ->label('Period')
                    ->suffix(' mos')
                    ->toggleable()
                    ->sortable(),
            ])
            ->defaultGroup('category')
            ->groups([
                Group::make('category')
                    ->label('Cost Category')
                    ->collapsible(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
