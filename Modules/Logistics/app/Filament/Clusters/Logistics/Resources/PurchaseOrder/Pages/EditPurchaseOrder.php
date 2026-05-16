<?php

namespace Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\PurchaseOrderResource;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
