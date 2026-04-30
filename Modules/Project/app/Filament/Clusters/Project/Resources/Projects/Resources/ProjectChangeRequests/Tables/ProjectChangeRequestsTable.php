<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Enums\ProjectChangeRequestType;
use Modules\Project\Enums\TaskStatus;

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
                    ->weight('bold')
                    ->color('primary')
                    ->copyable()
                    ->icon(Heroicon::OutlinedDocumentText),

                TextColumn::make('project.number')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->description(fn ($record) => $record->project?->name)
                    ->wrap(),

                TextColumn::make('type')
                    ->label('Request Type')
                    ->badge()
                    ->sortable(),

                TextColumn::make('notes')
                    ->label('Notes')
                    ->html()
                    ->limit(50)
                    ->color('gray')
                    ->searchable()
                    ->tooltip(fn ($record) => strip_tags($record->notes)),

                TextColumn::make('tasks.status')
                    ->label('Task Progress')
                    ->badge()
                    ->placeholder('Pending Approval')
                    ->alignCenter()
                    ->url(function ($record) {
                        $task = $record->tasks()->latest()->first();

                        return $task ? "/admin/projects/{$record->project_id}/tasks/{$task->id}/discussions" : null;
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('status')
                    ->label('PCR Status')
                    ->badge()
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime('d M Y')
                    ->description(fn ($record) => $record->created_at->diffForHumans())
                    ->sortable()
                    ->toggleable()
                    ->color('gray'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(ProjectChangeRequestType::class),

                SelectFilter::make('status')
                    ->options(ProjectChangeRequestStatus::class),

                SelectFilter::make('task_status')
                    ->label('Task Status')
                    ->options(TaskStatus::class)
                    ->query(function ($query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return $query->whereHas('tasks', fn ($q) => $q->where('status', $data['value']));
                    }),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Draft),
                    DeleteAction::make()
                        ->visible(fn ($record) => $record->status === ProjectChangeRequestStatus::Draft),
                ])
                    ->icon(Heroicon::OutlinedEllipsisVertical)
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No Change Requests')
            ->emptyStateDescription('This project currently has no change requests recorded.')
            ->emptyStateIcon(Heroicon::OutlinedDocumentDuplicate);
    }
}
