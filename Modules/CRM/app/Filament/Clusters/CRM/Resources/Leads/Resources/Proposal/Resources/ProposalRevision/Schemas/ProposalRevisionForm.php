<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Proposal\Resources\ProposalRevision\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProposalRevisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label(__('Document Number'))
                    ->disabled(),
                TextInput::make('sequence_number')
                    ->label(__('Sequence #'))
                    ->disabled(),
                TextInput::make('year')
                    ->disabled(),
                Textarea::make('reason')
                    ->columnSpanFull()
                    ->disabled(),
                KeyValue::make('snapshot')
                    ->label(__('Data Snapshot'))
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),

                Section::make(__('Document Snapshots'))
                    ->description(__('Documents that were active at the time of this revision.'))
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('final_proposal')
                            ->collection('final_proposal')
                            ->label(__('Draft Snapshot'))
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                        SpatieMediaLibraryFileUpload::make('signed_proposal')
                            ->collection('signed_proposal')
                            ->label(__('Signed Snapshot'))
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }
}
