<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\GeneralInformation\Schemas;

use App\Filament\Infolists\Components\DigitalSignatureEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GeneralInformationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('document_number'),
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('status')
                                    ->badge(),
                                TextEntry::make('scope_of_work')
                                    ->columnSpanFull(),
                            ]),
                        TextEntry::make('description')
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('estimated_start_date')
                                    ->date(),
                                TextEntry::make('estimated_end_date')
                                    ->date(),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Approval & Signatures')
                    ->schema([
                        DigitalSignatureEntry::make('signatures')
                            ->columnSpanFull(),
                    ])->columnSpanFull()
                    ->visible(fn ($record) => $record?->signatures()->exists()),
            ]);
    }
}
