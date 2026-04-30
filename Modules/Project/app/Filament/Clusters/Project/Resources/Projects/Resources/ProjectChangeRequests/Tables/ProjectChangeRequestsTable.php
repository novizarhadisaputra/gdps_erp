<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Tables;

use Filament\Actions;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Project\Enums\ProjectChangeRequestStatus;

class ProjectChangeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('PCR Number')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('project.number')
                    ->label('Project Number')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Request Type')
                    ->badge()
                    ->sortable()
                    ->searchable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->html()
                    ->limit(100)
                    ->searchable()
                    ->tooltip(fn ($record) => strip_tags($record->notes)),

                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Draft),
                    Actions\DeleteAction::make()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Draft),
                ]),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Change Requests')
            ->emptyStateDescription('This project currently has no change requests recorded.')
            ->emptyStateIcon('heroicon-o-document-duplicate');
    }
}
