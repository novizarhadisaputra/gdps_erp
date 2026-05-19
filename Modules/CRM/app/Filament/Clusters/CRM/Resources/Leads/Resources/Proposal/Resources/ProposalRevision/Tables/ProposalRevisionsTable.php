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
                TextColumn::make('number')
                    ->label(__('Proposal Number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sequence_number')
                    ->label(__('Rev #'))
                    ->formatStateUsing(fn ($state) => sprintf('REV/%02d', $state))
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('amount_snapshot')
                    ->label(__('Amount (Snapshot)'))
                    ->state(fn ($record) => $record->snapshot['amount'] ?? 0)
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label(__('Revision Reason'))
                    ->wrap()
                    ->limit(100),
                TextColumn::make('user.name')
                    ->label(__('Revised By'))
                    ->description(fn ($record) => $record->created_at->diffForHumans()),
                TextColumn::make('created_at')
                    ->label(__('Timestamp'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                ]),
            ]);
    }
}
