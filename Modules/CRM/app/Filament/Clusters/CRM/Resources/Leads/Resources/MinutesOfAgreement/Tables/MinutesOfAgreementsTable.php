<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\MinutesOfAgreementResource;
use Modules\CRM\Models\MinutesOfAgreement;

class MinutesOfAgreementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('moa_number')
                    ->label('Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('proposal.proposal_number')
                    ->label('Proposal')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('negotiation_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (MinutesOfAgreement $record) => MinutesOfAgreementResource::getUrl('view', ['lead' => $record->lead_id, 'record' => $record->id])),
            ]);
    }
}
