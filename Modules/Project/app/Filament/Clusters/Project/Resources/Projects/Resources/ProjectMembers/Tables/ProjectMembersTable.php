<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectMembers\Tables;

use Filament\Actions;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('memberable.name')
                    ->label('Member Name')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn ($record) => $record->memberable?->name ?? 'Unknown'),
                TextColumn::make('role')
                    ->label('Role')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('joined_at')
                    ->label('Joined At')
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
