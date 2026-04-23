<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Tables;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
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
                TextColumn::make('report_number')
                    ->label('Report Number')
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
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->money('IDR')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()
                        ->money('IDR')
                    ),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->relationship('project', 'code'),
                SelectFilter::make('status')
                    ->options(WorkCompletionStatus::class),
            ])
            ->recordActions([
                Actions\ActionGroup::make([
                    Actions\ViewAction::make(),
                    Actions\EditAction::make(),
                    Actions\Action::make('discussions')
                        ->label('Discussions')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->color('info')
                        ->url(fn ($record) => "/admin/projects/{$record->project_id}/work-completion-reports/{$record->id}/discussions"),
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
