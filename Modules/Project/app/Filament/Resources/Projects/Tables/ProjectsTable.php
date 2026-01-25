<?php

namespace Modules\Project\Filament\Resources\Projects\Tables;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planning' => 'gray',
                        'active' => 'success',
                        'completed' => 'primary',
                        'on hold' => 'warning',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('workScheme.name')
                    ->label('Skema Kerja')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('productCluster.name')
                    ->label('Cluster')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('tax.name')
                    ->label('Pajak')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('projectArea.name')
                    ->label('Area')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('paymentTerm.name')
                    ->label('TOP')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('projectType.name')
                    ->label('Project Type')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('billingOption.name')
                    ->label('Option')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('oprep.name')
                    ->label('OPREP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('ams.name')
                    ->label('AMS')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'planning' => 'Planning',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('client_id')
                    ->relationship('client', 'name')
                    ->label('Client')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('project_area_id')
                    ->relationship('projectArea', 'name')
                    ->label('Area')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                ExcelImportAction::make()
                    ->color('info'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make(),
                ]),
            ]);
    }
}
