<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\GeneralInformation\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class GeneralInformationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Customer & Project PIC')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'draft' => 'gray',
                                        'submitted' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('pic_customer_name')->label('PIC Customer'),
                                TextEntry::make('pic_customer_phone')->label('Phone'),
                            ]),
                    ]),
                Section::make('Project Details')
                    ->schema([
                        TextEntry::make('scope_of_work')->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('estimated_start_date')->date(),
                                TextEntry::make('estimated_end_date')->date(),
                            ]),
                        TextEntry::make('location'),
                    ]),
                Section::make('Documents')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('feasibility_study_document')
                                    ->label('Feasibility Study')
                                    ->state(fn ($record) => $record->getFirstMedia('feasibility_study')?->file_name ?? 'No FS Document')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('feasibility_study'), true)
                                    ->icon(Heroicon::OutlinedDocumentCheck)
                                    ->color(fn ($state) => $state === 'No FS Document' ? 'gray' : 'primary'),
                                TextEntry::make('rr_document_entry')
                                    ->label('RR Document')
                                    ->state(fn ($record) => $record->getFirstMedia('rr_document')?->file_name ?? 'No RR Document')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('rr_document'), true)
                                    ->icon(Heroicon::OutlinedClipboardDocument)
                                    ->color(fn ($state) => $state === 'No RR Document' ? 'gray' : 'primary'),
                            ]),
                    ]),
                Section::make('Digital Signatures')
                    ->schema([
                        \Filament\Infolists\Components\ViewEntry::make('signatures')
                            ->view('filament.infolists.digital-signature')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->signatures)),
            ]);
    }
}
