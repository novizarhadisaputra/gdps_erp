<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class ListProfitabilityAnalyses extends ListRecords
{
    protected static string $resource = ProfitabilityAnalysisResource::class;

    public function getSubheading(): ?string
    {
        return 'Financial feasibility studies and margin analysis for leads.';
    }
}
