<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Resources\ProfitabilityAnalysisUpdate\ProfitabilityAnalysisUpdateResource;

class CreateProfitabilityAnalysisUpdate extends CreateRecord
{
    protected static string $resource = ProfitabilityAnalysisUpdateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $actual = $this->getParent();
        
        $data['user_id'] = auth()->id();
        $data['profitability_analysis_actual_id'] = $actual->id;
        $data['profitability_analysis_id'] = $actual->profitability_analysis_id;
        $data['month'] = $actual->month;
        $data['year'] = $actual->year;
        
        return $data;
    }
}
