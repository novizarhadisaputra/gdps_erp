<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\PurchaseRequestResource;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;
}
