<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProfitabilityAnalysisUpdateForm
{
    public static function configure(Schema $schema, $record = null): Schema
    {
        return $schema
            ->components([
                TextInput::make('projected_revenue')
                    ->label('Projected Revenue (IDR)')
                    ->numeric()
                    ->required()
                    ->default(fn () => $record?->weeklyUpdates()->latest()->first()?->projected_revenue ?? $record?->revenue_per_month),
                Textarea::make('notes')
                    ->label('Update Notes')
                    ->placeholder('Explain reasons for the projection change...')
                    ->required(),
            ]);
    }
}
