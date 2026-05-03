<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProposalRevisionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('revision_number')
                    ->label('Revision #')
                    ->sortable(),
                TextColumn::make('sequence_number')
                    ->label('Sequence #')
                    ->sortable(),
                TextColumn::make('year')
                    ->sortable(),
                TextColumn::make('reason')
                    ->limit(50),
                TextColumn::make('user.name')
                    ->label('Revised By'),
                TextColumn::make('created_at')
                    ->label('Revision Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ]);
    }
}
