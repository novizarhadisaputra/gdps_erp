<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\Pages;

use Filament\Actions;

use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\WorkOrders\WorkOrderResource;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
