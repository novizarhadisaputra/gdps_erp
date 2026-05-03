<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Project\Enums\WorkCompletionStatus;

class WorkCompletionReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Report Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.number')
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
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('IDR')
                    ),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->relationship('project', 'number'),
                SelectFilter::make('status')
                    ->options(WorkCompletionStatus::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === WorkCompletionStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === WorkCompletionStatus::Draft),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
