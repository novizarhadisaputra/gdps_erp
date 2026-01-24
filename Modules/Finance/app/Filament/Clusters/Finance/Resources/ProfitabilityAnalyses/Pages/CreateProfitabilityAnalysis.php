<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\ProfitabilityAnalysisResource;

class CreateProfitabilityAnalysis extends CreateRecord
{
    protected static string $resource = ProfitabilityAnalysisResource::class;
}
