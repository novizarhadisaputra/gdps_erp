<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\WarehouseResource;

class ListWarehouses extends ListRecords
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
