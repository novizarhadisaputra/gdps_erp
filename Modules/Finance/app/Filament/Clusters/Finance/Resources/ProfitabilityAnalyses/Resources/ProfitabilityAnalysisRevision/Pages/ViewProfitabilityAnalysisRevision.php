<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisRevision\ProfitabilityAnalysisRevisionResource;

class ViewProfitabilityAnalysisRevision extends ViewRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisRevisionResource::class;
}
