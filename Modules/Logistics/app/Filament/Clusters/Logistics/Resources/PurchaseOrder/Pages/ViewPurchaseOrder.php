<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\PurchaseOrderResource;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Traits\HasPurchaseOrderActions;

class ViewPurchaseOrder extends ViewRecord
{
    use HasPurchaseOrderActions;

    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getPurchaseOrderHeaderActions();
    }
}
