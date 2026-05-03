<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\MinimumWages\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\MasterData\Enums\MinimumWageType;
use pxlrbt\FilamentExcel\Actions\ExportAction;

class MinimumWagesTable
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
                    ->color(fn (MinimumWageType $state): string => match ($state) {
                        MinimumWageType::Regency => 'info',
                        MinimumWageType::City => 'warning',
                        MinimumWageType::Province => 'success',
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
                    ->label('Active Status')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->icon(fn ($state) => $state ? 'heroicon-o-star' : null)
                    ->color('warning'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    ExportAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
