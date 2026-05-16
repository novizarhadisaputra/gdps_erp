<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\WarehouseResource;

class ViewWarehouse extends ViewRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
