<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\DirectCostCategories\DirectCostCategoryResource;

class CreateDirectCostCategory extends CreateRecord
{
    protected static string $resource = DirectCostCategoryResource::class;
}
