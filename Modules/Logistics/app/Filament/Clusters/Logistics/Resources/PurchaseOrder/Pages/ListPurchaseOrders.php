<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\PurchaseOrderResource;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
