<?php

namespace Modules\CRM\Filament\Resources\Contracts\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
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
                    ]),
                Section::make('Status')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('reminder_status')
                                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', $state))
                                    ->badge()
                                    ->color('gray'),
                                TextEntry::make('termination_reason')
                                    ->visible(fn (Contract $record) => $record->status === ContractStatus::Terminated)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
