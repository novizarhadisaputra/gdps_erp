<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class ViewProfitabilityAnalysis extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;
}
