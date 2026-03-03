<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;

class ViewProfitabilityAnalysis extends ViewRecord
{
    use InteractsWithParentRecord;
    use HasProfitabilityAnalysisActions;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...$this->getProfitabilityAnalysisActions(),
        ];
    }
}
