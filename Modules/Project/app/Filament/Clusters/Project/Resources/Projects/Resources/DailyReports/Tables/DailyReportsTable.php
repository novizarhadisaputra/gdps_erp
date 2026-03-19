<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
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
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => $record->status === DailyReportStatus::Draft),
                Action::make('submit')
                    ->label('Submit')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('warning')
                    ->visible(fn ($record) => $record->status === DailyReportStatus::Draft)
                    ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Submitted])),
                Action::make('approve')
                    ->label('Approve')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn ($record) => $record->status === DailyReportStatus::Submitted && auth()->user()->can('approve', $record))
                    ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Approved])),
                Action::make('reject')
                    ->label('Reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === DailyReportStatus::Submitted && auth()->user()->can('approve', $record))
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['status' => DailyReportStatus::Rejected])),
                Action::make('discussions')
                    ->label('Discussions')
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->color('info')
                    ->url(fn ($record) => "/admin/projects/{$record->project_id}/daily-reports/{$record->id}/discussions"),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
