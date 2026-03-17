<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Tables\ProfitabilityAnalysisRevisionsTable;

class ManageProfitabilityAnalysisRevisions extends ManageRelatedRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected static string $relationship = 'revisions';

    protected static ?string $relatedResource = ProfitabilityAnalysisRevisionResource::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';

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
