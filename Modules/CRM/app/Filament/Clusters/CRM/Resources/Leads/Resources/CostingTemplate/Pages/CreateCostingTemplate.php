<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Schemas\CostingTemplateForm;

class CreateCostingTemplate extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = CostingTemplateResource::class;

    protected function afterFill(): void
    {
        $this->form->fill(
            CostingTemplateForm::getAutoFillData($this->parentRecord)
        );
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['lead_id'] = $this->parentRecord->id;

        return $data;
    }
}
