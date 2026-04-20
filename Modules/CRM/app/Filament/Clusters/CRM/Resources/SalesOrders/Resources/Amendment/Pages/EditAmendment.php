<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;

class EditAmendment extends EditRecord
{
    protected static string $resource = AmendmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord(), 'parent' => $this->getRecord()->sales_order_id]);
    }
}
