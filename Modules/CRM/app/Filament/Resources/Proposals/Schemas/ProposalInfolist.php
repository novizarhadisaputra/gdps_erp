<?php

namespace Modules\CRM\Filament\Resources\Proposals\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

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
                    ]),
                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge(),
                    ]),
            ]);
    }
}
