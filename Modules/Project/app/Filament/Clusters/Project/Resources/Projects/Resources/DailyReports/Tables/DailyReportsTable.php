<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Tables;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Project\Enums\DailyReportStatus;

class DailyReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')
                    ->date()
                    ->sortable(),
                TextColumn::make('reportedBy.id')
                    ->label('Reported By')
                    ->getStateUsing(fn ($record) => $record->reportedBy?->memberable?->name ?? 'Unknown')
                    ->searchable(),
                TextColumn::make('task.name')
                    ->label('Related Task')
                    ->placeholder('No specific task')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('weather')
                    ->badge()
                    ->color('info'),
                TextColumn::make('content')
                    ->label('Summary')
                    ->limit(50),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make()
                        ->visible(fn ($record) => $record->status === DailyReportStatus::Draft),
                    Actions\Action::make('submit')
                        ->label('Submit')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->color('warning')
                        ->visible(fn ($record) => $record->status === DailyReportStatus::Draft)
                        ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Submitted])),
                    Actions\Action::make('approve')
                        ->label('Approve')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->visible(fn ($record) => $record->status === DailyReportStatus::Submitted && auth()->user()->can('approve', $record))
                        ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Approved])),
                    Actions\Action::make('reject')
                        ->label('Reject')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->visible(fn ($record) => $record->status === DailyReportStatus::Submitted && auth()->user()->can('approve', $record))
                        ->requiresConfirmation()
                        ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Rejected])),
                    Actions\Action::make('discussions')
                        ->label('Discussions')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->color('info')
                        ->url(fn ($record) => "/admin/projects/{$record->project_id}/daily-reports/{$record->id}/discussions"),
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
