<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Modules\CRM\Models\Customer;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Customer Information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('Code'))
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('name')
                                    ->label(__('Name')),
                                TextEntry::make('legal_entity_type')
                                    ->label(__('Legal Entity')),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('Contacts'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('email'),
                                TextEntry::make('phone'),
                                TextEntry::make('address'),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('Documents'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('npwp_document')
                                    ->label(__('NPWP'))
                                    ->state(fn (Customer $record) => $record->getFirstMedia('npwp')?->file_name ?? 'No NPWP')
                                    ->url(fn (Customer $record) => $record->getFirstMediaUrl('npwp'), true)
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->color(fn ($state) => $state === 'No NPWP' ? 'gray' : 'primary'),
                                TextEntry::make('legal_docs_count')
                                    ->label(__('Legal Documents'))
                                    ->state(fn (Customer $record) => $record->getMedia('legal_documents')->count().' File(s)')
                                    ->url(fn (Customer $record) => $record->getFirstMediaUrl('legal_documents'), true)
                                    ->icon(Heroicon::OutlinedScale)
                                    ->color(fn (Customer $record) => $record->getMedia('legal_documents')->count() > 0 ? 'primary' : 'gray'),
                                TextEntry::make('company_profile_doc')
                                    ->label(__('Company Profile'))
                                    ->state(fn (Customer $record) => $record->getFirstMedia('company_profile')?->file_name ?? 'No Profile')
                                    ->url(fn (Customer $record) => $record->getFirstMediaUrl('company_profile'), true)
                                    ->icon(Heroicon::OutlinedBuildingOffice)
                                    ->color(fn ($state) => $state === 'No Profile' ? 'gray' : 'primary'),
                            ]),
                    ])->columnSpanFull(),
                Section::make(__('GL Account Mapping'))
                    ->description(__('Summary of SAP General Ledger account mappings for this customer.'))
                    ->schema([
                        RepeatableEntry::make('accountMappings')
                            ->label(__(''))
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('type')
                                            ->badge()
                                            ->color(fn (string $state): string => match ($state) {
                                                'accrual' => 'info',
                                                'revenue' => 'success',
                                                'receivable' => 'warning',
                                                'expense' => 'danger',
                                                default => 'gray',
                                            })
                                            ->formatStateUsing(fn (string $state): string => str($state)->headline()),
                                        TextEntry::make('revenueType.name')
                                            ->label(__('Revenue Type'))
                                            ->placeholder(__('All Types')),
                                        TextEntry::make('revenueSegment.name')
                                            ->label(__('Segment'))
                                            ->placeholder(__('All Segments')),
                                        TextEntry::make('chartOfAccount.name')
                                            ->label(__('GL Account'))
                                            ->state(fn ($record) => $record->chartOfAccount ? "[{$record->chartOfAccount->code}] {$record->chartOfAccount->name}" : '-'),
                                    ]),
                            ])
                            ->columns(1)
                            ->placeholder(__('No account mappings defined for this customer.')),
                    ])->columnSpanFull(),
            ]);
    }
}
