<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\PurchaseOrderResource;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;
}
