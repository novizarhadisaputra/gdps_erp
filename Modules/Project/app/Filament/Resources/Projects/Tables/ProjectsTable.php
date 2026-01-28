<?php

namespace Modules\Project\Filament\Resources\Projects\Tables;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planning' => 'gray',
                        'active' => 'success',
                        'completed' => 'primary',
                        'on hold' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('workScheme.name')
                    ->label('Skema Kerja')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productCluster.name')
                    ->label('Cluster')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tax.name')
                    ->label('Pajak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('projectArea.name')
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('paymentTerm.name')
                    ->label('TOP')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('projectType.name')
                    ->label('Project Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('billingOption.name')
                    ->label('Option')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('oprep.name')
                    ->label('OPREP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('ams.name')
                    ->label('AMS')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ]),
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('project_area_id')
                    ->relationship('projectArea', 'name')
                    ->label('Area')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->color('info'),
            ]);
    }
}
