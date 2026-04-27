<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages;

use Filament\Actions;

use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\WorkOrderResource;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
