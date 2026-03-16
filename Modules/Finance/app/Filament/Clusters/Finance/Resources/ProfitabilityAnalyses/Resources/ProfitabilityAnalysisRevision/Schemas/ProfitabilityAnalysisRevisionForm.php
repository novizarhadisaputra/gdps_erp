<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisRevisionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('revision_number')
                    ->label('Revision #')
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
            ]);
    }
}
