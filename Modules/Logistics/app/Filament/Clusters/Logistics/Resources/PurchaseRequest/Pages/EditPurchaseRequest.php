<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\PurchaseRequestResource;

class EditPurchaseRequest extends EditRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
