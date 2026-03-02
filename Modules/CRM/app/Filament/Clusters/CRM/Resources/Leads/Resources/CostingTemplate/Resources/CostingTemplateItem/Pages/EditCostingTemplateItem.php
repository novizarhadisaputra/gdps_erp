<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Resources\CostingTemplateItem\CostingTemplateItemResource;

class EditCostingTemplateItem extends EditRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = CostingTemplateItemResource::class;
}
