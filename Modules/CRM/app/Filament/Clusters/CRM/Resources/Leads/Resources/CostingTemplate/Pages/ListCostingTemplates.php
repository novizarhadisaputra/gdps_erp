<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;
use Modules\CRM\Traits\CanImportAi;

class ListCostingTemplates extends ListRecords
{
    use CanImportAi;

    protected function getHeaderActions(): array
    {
        return [
            $this->getImportCostingAiAction(),
            CreateAction::make(),
        ];
    }

    protected static string $resource = CostingTemplateResource::class;
}
