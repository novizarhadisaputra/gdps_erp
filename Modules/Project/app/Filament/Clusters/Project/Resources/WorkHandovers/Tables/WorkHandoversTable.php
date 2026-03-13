<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkHandovers\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Modules\Project\Enums\WorkHandoverStatus;

class WorkHandoversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('handover_number')
                    ->label('Handover Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.code')
                    ->label('Project Code')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('document_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('period')
                    ->label('Service Period')
                    ->getStateUsing(fn ($record) => "{$record->service_period_start->format('d/m/Y')} - {$record->service_period_end->format('d/m/Y')}")
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->relationship('project', 'code'),
                SelectFilter::make('status')
                    ->options(WorkHandoverStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
