<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;

class ViewPurchaseOrder extends ViewRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
