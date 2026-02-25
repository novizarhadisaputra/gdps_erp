<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostingTemplateItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('monthly_cost')
                    ->money('IDR')
                    ->sortable(),
            ]);
    }
}
