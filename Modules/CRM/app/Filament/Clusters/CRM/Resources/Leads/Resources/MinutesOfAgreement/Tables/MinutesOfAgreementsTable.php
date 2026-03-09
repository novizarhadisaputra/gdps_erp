<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\MinutesOfAgreement\Tables;

use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\CRM\Enums\MoAStatus;
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
                Action::make('sign')
                    ->label('Sign MoA')
                    ->icon('heroicon-o-pencil-square')
                    ->color('success')
                    ->visible(fn (MinutesOfAgreement $record) => $record->status !== MoAStatus::Approved)
                    ->requireSignature(
                        documentName: fn (MinutesOfAgreement $record) => "Minutes of Agreement {$record->moa_number}",
                        propertyName: 'status',
                        targetValue: MoAStatus::Approved
                    ),
            ]);
    }
}
