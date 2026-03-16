<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;

class ListProfitabilityAnalysisRevisions extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisRevisionResource::class;

    public function getSubheading(): ?string
    {
        return 'View previous revisions of this profitability analysis.';
    }
}
