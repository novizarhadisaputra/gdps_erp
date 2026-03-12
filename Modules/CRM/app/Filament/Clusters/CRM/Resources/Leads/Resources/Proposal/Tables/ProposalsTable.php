<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\CRM\Enums\ProposalStatus;

class ProposalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('workScheme.name')
                    ->label('Scheme')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal_number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('submission_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->label('Customer')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->options(ProposalStatus::class),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('Revise')
                    ->label('Revise')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->placeholder('Are you sure you want to revise this proposal? This will reset the status to Draft and allow you to modify the Profitability Analysis costing.')
                    ->action(fn ($record) => $record->update(['status' => \Modules\CRM\Enums\ProposalStatus::Draft]))
                    ->visible(fn ($record) => $record->status === \Modules\CRM\Enums\ProposalStatus::Submitted || $record->status === 'submitted'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
