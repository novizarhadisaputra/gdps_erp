<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Proposals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ProposalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Proposal Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('proposal_number')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('amount')
                                    ->money('IDR'),
                                TextEntry::make('submission_date')
                                    ->date(),
                            ]),
                        Grid::make(1)
                            ->schema([
                                TextEntry::make('final_proposal_document')
                                    ->label('Proposal Document')
                                    ->state(fn ($record) => $record->getFirstMedia('final_proposal')?->file_name ?? 'No Document')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('final_proposal'), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Document' ? 'gray' : 'primary'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Approval & Signatures')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge(),
                            ]),
                        \App\Filament\Infolists\Components\DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record->signatures()->exists()),
            ]);
    }
}
