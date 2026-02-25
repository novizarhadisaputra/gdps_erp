<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\CostingTemplateItemResource;

class ListCostingTemplateItems extends ListRecords
{
    protected static string $resource = CostingTemplateItemResource::class;
}
