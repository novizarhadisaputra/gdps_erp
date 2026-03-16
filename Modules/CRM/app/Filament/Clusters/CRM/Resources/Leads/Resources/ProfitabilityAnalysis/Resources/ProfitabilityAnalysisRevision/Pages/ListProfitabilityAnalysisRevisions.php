<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;

class ListProfitabilityAnalysisRevisions extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource::class;

    public function getSubheading(): ?string
    {
        return 'View previous revisions of this profitability analysis.';
    }
}
