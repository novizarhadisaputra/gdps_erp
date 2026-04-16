<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\ProfitabilityAnalysisActualResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\ProfitabilityAnalysisUpdateResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Tables\ProfitabilityAnalysisUpdatesTable;

class ManageWeeklyUpdates extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisActualResource::class;

    protected static string $relationship = 'weeklyUpdates';

    protected static ?string $relatedResource = ProfitabilityAnalysisUpdateResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?string $title = 'Weekly Projections';

    public static function getNavigationLabel(): string
    {
        return 'Weekly Projections';
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisUpdatesTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->label('Add Weekly Projection'),
            ]);
    }
}
