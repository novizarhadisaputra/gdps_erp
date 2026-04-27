<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CooperationAgreement\CooperationAgreementResource;
use Modules\CRM\Models\CooperationAgreement;

class CooperationAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.number')
                    ->label('Proposal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable()
                    ->alignRight(),
                TextColumn::make('agreement_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->url(fn (CooperationAgreement $record) => CooperationAgreementResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id])),
                    EditAction::make()
                        ->url(fn (CooperationAgreement $record) => CooperationAgreementResource::getUrl('edit', ['lead' => $record->lead_id, 'record' => $record->id])),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
