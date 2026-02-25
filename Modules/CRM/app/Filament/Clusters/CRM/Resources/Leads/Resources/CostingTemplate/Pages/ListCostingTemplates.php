<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource;

class ListCostingTemplates extends ListRecords
{
    protected static string $resource = CostingTemplateResource::class;
}
