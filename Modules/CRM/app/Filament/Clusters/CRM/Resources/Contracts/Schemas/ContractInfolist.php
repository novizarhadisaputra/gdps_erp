<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Contracts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Models\Contract;

class ContractInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contract Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('contract_number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('proposal.proposal_number')
                                    ->label('Reference Proposal'),
                                TextEntry::make('expiry_date')
                                    ->date(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('signed_contract_document')
                                    ->label('Signed Contract')
                                    ->state(fn ($record) => $record->getFirstMedia('signed_contract')?->file_name ?? 'No Document')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('signed_contract'), true)
                                    ->icon(Heroicon::OutlinedDocumentCheck)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                                TextEntry::make('termination_evidence_document')
                                    ->label('Termination Evidence')
                                    ->state(fn ($record) => $record->getFirstMedia('termination_evidence')?->file_name ?? 'No Evidence')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('termination_evidence'), true)
                                    ->icon(Heroicon::OutlinedArchiveBoxXMark)
                                    ->color(fn ($state) => $state === 'No Evidence' ? 'gray' : 'danger')
                                    ->visible(fn (Contract $record) => $record->status === ContractStatus::Terminated),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Approval & Signatures')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('reminder_status')
                                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', $state))
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('termination_reason')
                                    ->visible(fn (Contract $record) => $record->status === ContractStatus::Terminated),
                            ]),
                        \App\Filament\Infolists\Components\DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record->signatures()->exists()),
            ]);
    }
}
