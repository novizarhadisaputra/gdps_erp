<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;

class EditPurchaseOrder extends EditRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
