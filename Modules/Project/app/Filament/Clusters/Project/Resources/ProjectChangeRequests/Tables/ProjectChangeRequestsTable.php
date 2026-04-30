<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Tables;

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

                    Actions\Action::make('submit')
                        ->label('Submit')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Draft)
                        ->action(fn ($record) => $record->update(['status' => ProjectChangeRequestStatus::Submitted])),

                    Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Submitted)
                        ->action(fn ($record) => $record->update(['status' => ProjectChangeRequestStatus::Approved])),

                    Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Submitted)
                        ->form([
                            \Filament\Forms\Components\Textarea::make('reason')
                                ->label('Reason')
                                ->required(),
                        ])
                        ->action(fn ($record) => $record->update(['status' => ProjectChangeRequestStatus::Rejected])),

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
