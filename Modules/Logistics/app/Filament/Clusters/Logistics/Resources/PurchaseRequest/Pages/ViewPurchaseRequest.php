<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\PurchaseRequestResource;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Traits\HasPurchaseRequestActions;

class ViewPurchaseRequest extends ViewRecord
{
    use HasPurchaseRequestActions;

    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getPurchaseRequestHeaderActions();
    }
}
