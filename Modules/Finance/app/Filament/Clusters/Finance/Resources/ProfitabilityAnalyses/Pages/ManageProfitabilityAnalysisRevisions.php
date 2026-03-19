<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Tables\ProfitabilityAnalysisRevisionsTable;

class ManageProfitabilityAnalysisRevisions extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $relationship = 'revisions';

    protected static ?string $relatedResource = ProfitabilityAnalysisRevisionResource::class;

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $title = 'Revision History';

    public function getSubheading(): ?string
    {
        return 'View and manage previous versions of this profitability analysis.';
    }

    public function table(Table $table): Table
    {
        return ProfitabilityAnalysisRevisionsTable::configure($table);
    }
}
