<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\WarehouseResource;

class CreateWarehouse extends CreateRecord
{
    protected static string $resource = WarehouseResource::class;
}
