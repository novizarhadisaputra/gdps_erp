<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\CostingTemplateResource;

class CreateCostingTemplate extends CreateRecord
{
    protected static string $resource = CostingTemplateResource::class;
}
