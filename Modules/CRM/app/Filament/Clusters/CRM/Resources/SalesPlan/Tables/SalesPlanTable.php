<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SalesPlanTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.title')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ams.name')
                    ->label('AMS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('revenueSegment.name')
                    ->label('Segment')
                    ->sortable(),
                TextColumn::make('productCluster.name')
                    ->label('Cluster')
                    ->sortable(),
                TextColumn::make('estimated_value')
                    ->label('Value')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('priority_level')
                    ->label('Priority')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'success',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('confidence_level')
                    ->label('Confidence')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'optimistic' => 'success',
                        'moderate' => 'warning',
                        'pessimistic' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
            ]);
    }
}
