<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;

class CreateCostingTemplate extends CreateRecord
{
    protected static string $resource = CostingTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['lead_id'] = $this->getOwnerRecord()->id;

        return $data;
    }
}
