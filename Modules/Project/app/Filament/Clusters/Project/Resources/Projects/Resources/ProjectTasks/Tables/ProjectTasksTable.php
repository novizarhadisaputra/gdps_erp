<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Tables;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectTasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Task Name')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->parent ? "Parent: {$record->parent->name}" : null),
                TextColumn::make('assignedMember.id')
                    ->label('Assigned To')
                    ->getStateUsing(fn ($record) => $record->assignedMember?->memberable?->name ?? 'Unassigned')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('priority')
                    ->badge(),
                TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('discussions')
                        ->label('Discussions')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->color('info')
                        ->url(fn ($record) => "/admin/projects/{$record->project_id}/tasks/{$record->id}/discussions"),
                    Actions\DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Actions'),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
