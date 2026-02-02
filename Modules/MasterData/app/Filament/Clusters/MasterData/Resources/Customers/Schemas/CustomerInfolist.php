<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Code')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('name')
                                    ->label('Name'),
                                TextEntry::make('legal_entity_type')
                                    ->label('Legal Entity'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Contacts')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('email'),
                                TextEntry::make('phone'),
                                TextEntry::make('address'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Documents')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('npwp_document')
                                    ->label('NPWP')
                                    ->state(fn ($record) => $record->getFirstMedia('npwp')?->file_name ?? 'No NPWP')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('npwp'), true)
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->color(fn ($state) => $state === 'No NPWP' ? 'gray' : 'primary'),
                                TextEntry::make('legal_docs_count')
                                    ->label('Legal Documents')
                                    ->state(fn ($record) => $record->getMedia('legal_documents')->count().' File(s)')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('legal_documents'), true)
                                    ->icon(Heroicon::OutlinedScale)
                                    ->color(fn ($record) => $record->getMedia('legal_documents')->count() > 0 ? 'primary' : 'gray'),
                                TextEntry::make('company_profile_doc')
                                    ->label('Company Profile')
                                    ->state(fn ($record) => $record->getFirstMedia('company_profile')?->file_name ?? 'No Profile')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('company_profile'), true)
                                    ->icon(Heroicon::OutlinedBuildingOffice)
                                    ->color(fn ($state) => $state === 'No Profile' ? 'gray' : 'primary'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
