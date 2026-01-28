<?php

namespace Modules\MasterData\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal & Job Details')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Employee ID')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('name')
                                    ->label('Full Name'),
                                TextEntry::make('email')
                                    ->label('Email'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('position')
                                    ->label('Position'),
                                TextEntry::make('department')
                                    ->label('Department'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => $state === 'active' ? 'success' : 'gray'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Documents')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('identity_card_doc')
                                    ->label('Identity Card')
                                    ->state(fn ($record) => $record->getFirstMedia('identity_card')?->file_name ?? 'No ID Card')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('identity_card'), true)
                                    ->icon(Heroicon::OutlinedIdentification)
                                    ->color(fn ($state) => $state === 'No ID Card' ? 'gray' : 'primary'),
                                TextEntry::make('resume_doc')
                                    ->label('Resume/CV')
                                    ->state(fn ($record) => $record->getFirstMedia('resume')?->file_name ?? 'No Resume')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('resume'), true)
                                    ->icon(Heroicon::OutlinedDocumentText)
                                    ->color(fn ($state) => $state === 'No Resume' ? 'gray' : 'primary'),
                                TextEntry::make('employment_contract_doc')
                                    ->label('Employment Contract')
                                    ->state(fn ($record) => $record->getFirstMedia('employment_contract')?->file_name ?? 'No Contract')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('employment_contract'), true)
                                    ->icon(Heroicon::OutlinedBriefcase)
                                    ->color(fn ($state) => $state === 'No Contract' ? 'gray' : 'primary'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
