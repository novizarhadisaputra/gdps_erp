<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\PurchaseOrder\PurchaseOrderResource;

class CreatePurchaseOrder extends CreateRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lead = $this->parentRecord;

        if ($lead) {
            $data['lead_id'] = $lead->id;
            $data['customer_id'] = $lead->customer_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['lead' => $this->parentRecord, 'record' => $this->record]);
    }
}
