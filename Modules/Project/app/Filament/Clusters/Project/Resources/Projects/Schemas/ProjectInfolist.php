<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('code')
                                    ->label('Project Code')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('name')
                                    ->label('Project Name'),
                                TextEntry::make('status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'planning' => 'gray',
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'on hold' => 'warning',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Stakeholders')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('customer.name')
                                    ->label('Customer'),
                                TextEntry::make('oprep.name')
                                    ->label('OPREP'),
                                TextEntry::make('ams.name')
                                    ->label('AMS'),
                            ]),
                    ])->columnSpanFull(),
                Section::make('Documents & Deliverables')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('project_documents_list')
                                    ->label('Project Documents')
                                    ->state(fn ($record) => $record->getMedia('project_documents')->map(fn ($m) => $m->file_name)->join(', ') ?: 'No Documents')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('project_documents'), true) // Just linking first for now or we could use custom html
                                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                                    ->color(fn ($state) => $state === 'No Documents' ? 'gray' : 'primary'),
                                TextEntry::make('deliverables_list')
                                    ->label('Deliverables')
                                    ->state(fn ($record) => $record->getMedia('deliverables')->map(fn ($m) => $m->file_name)->join(', ') ?: 'No Deliverables')
                                    ->url(fn ($record) => $record->getFirstMediaUrl('deliverables'), true)
                                    ->icon(Heroicon::OutlinedInboxArrowDown)
                                    ->color(fn ($state) => $state === 'No Deliverables' ? 'gray' : 'success'),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }
}
