<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis as BasePage;

class SummaryProfitabilityAnalysis extends BasePage
{
    protected static string $resource = ProfitabilityAnalysisResource::class;
}
