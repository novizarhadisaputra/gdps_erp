<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JobPositions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JobPositionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('basic_salary')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('risk_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'very_low' => 'gray',
                        'low' => 'info',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'very_high' => 'danger',
                        default => 'gray',
                    }),
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
