<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisRevisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Document Number')
                    ->disabled(),
                TextInput::make('sequence_number')
                    ->label('Sequence #')
                    ->disabled(),
                TextInput::make('year')
                    ->disabled(),
                Textarea::make('reason')
                    ->columnSpanFull()
                    ->disabled(),
                KeyValue::make('snapshot')
                    ->label('Data Snapshot')
                    ->columnSpanFull()
                    ->disabled()
                    ->dehydrated(false),

                Section::make('Document Snapshots')
                    ->description('Documents that were active at the time of this revision.')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('manpower_costing_backup')
                            ->collection('manpower_costing_backup')
                            ->label('Manpower Costing Snapshot')
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                        SpatieMediaLibraryFileUpload::make('operational_costing_backup')
                            ->collection('operational_costing_backup')
                            ->label('Operational Costing Snapshot')
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                        SpatieMediaLibraryFileUpload::make('tor')
                            ->collection('tor')
                            ->label('TOR Snapshot')
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                        SpatieMediaLibraryFileUpload::make('cogs_source')
                            ->collection('cogs_source')
                            ->label('COGS Snapshot')
                            ->disk('s3')
                            ->visibility('private')
                            ->multiple()
                            ->disabled()
                            ->dehydrated(false),
                    ])->columns(2),
            ]);
    }
}
