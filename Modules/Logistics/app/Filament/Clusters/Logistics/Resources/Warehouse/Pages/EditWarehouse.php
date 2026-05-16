<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\Warehouse\WarehouseResource;

class EditWarehouse extends EditRecord
{
    protected static string $resource = WarehouseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
