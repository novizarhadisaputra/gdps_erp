<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\PurchaseRequestResource;

class ListPurchaseRequests extends ListRecords
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
