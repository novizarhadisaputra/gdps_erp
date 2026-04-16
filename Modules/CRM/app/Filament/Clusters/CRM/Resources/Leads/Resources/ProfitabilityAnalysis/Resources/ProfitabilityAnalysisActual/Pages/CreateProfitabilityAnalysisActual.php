<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Resources\ProfitabilityAnalysisActual\ProfitabilityAnalysisActualResource;

class CreateProfitabilityAnalysisActual extends CreateRecord
{
    protected static string $resource = ProfitabilityAnalysisActualResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['month'] = $data['month'] ?? now()->month;
        $data['year'] = $data['year'] ?? now()->year;

        return $data;
    }
}
