<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\CostingTemplateItemResource;

class CreateCostingTemplateItem extends CreateRecord
{
    protected static string $resource = CostingTemplateItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['costing_template_id'] = $this->getOwnerRecord()->id;

        return $data;
    }
}
