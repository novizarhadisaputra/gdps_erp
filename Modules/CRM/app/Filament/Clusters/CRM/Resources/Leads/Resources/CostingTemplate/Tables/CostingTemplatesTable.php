<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CostingTemplatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pic.name')
                    ->label('PIC')
                    ->sortable(),
                TextColumn::make('total_monthly_cost')
                    ->label('Monthly Cost')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ]);
    }
}
