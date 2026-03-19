<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectTasks\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

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
                ViewAction::make(),
                EditAction::make(),
                Action::make('discussions')
                    ->label('Discussions')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('info')
                    ->url(fn ($record) => "/admin/projects/{$record->project_id}/tasks/{$record->id}/discussions"),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
