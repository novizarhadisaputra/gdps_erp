<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\WorkEquipments\WorkEquipmentResource;

class ListWorkEquipments extends ListRecords
{
    protected static string $resource = WorkEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
