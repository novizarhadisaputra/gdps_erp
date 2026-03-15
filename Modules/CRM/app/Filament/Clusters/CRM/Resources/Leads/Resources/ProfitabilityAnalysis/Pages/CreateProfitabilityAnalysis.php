<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\ProfitabilityAnalysisResource;

class CreateProfitabilityAnalysis extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProfitabilityAnalysisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index', ['lead' => $this->parentRecord]);
    }
}
