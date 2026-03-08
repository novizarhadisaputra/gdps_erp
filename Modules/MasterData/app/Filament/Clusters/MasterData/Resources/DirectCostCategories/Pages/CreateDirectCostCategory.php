<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\DirectCostCategories\DirectCostCategoryResource;

class CreateDirectCostCategory extends CreateRecord
{
    protected static string $resource = DirectCostCategoryResource::class;
}
