<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;

class LeadInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lead Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('title')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('workScheme.name')
                                    ->label('Work Scheme'),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('estimated_amount')
                                    ->money('IDR'),
                                TextEntry::make('start_date')
                                    ->date(),
                                TextEntry::make('end_date')
                                    ->date(),
                                TextEntry::make('expected_closing_date')
                                    ->date(),
                                TextEntry::make('user.name')
                                    ->label('Sales PIC'),
                            ]),
                        TextEntry::make('description')
                            ->columnSpanFull()
                            ->markdown(),
                    ])->columnSpanFull(),
            ]);
    }
}
