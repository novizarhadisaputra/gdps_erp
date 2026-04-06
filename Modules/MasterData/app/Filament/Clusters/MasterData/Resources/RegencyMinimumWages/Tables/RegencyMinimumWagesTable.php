<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\MasterData\Enums\RegencyMinimumWageType;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class RegencyMinimumWagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('province')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (RegencyMinimumWageType $state): string => match ($state) {
                        RegencyMinimumWageType::Regency => 'info',
                        RegencyMinimumWageType::City => 'warning',
                        RegencyMinimumWageType::Province => 'success',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('projectArea.name')
                    ->label('Project Area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('year')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Monthly Wage (UMK)')
                    ->money('IDR', locale: 'id')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
