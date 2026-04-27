<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\WorkOrder\WorkOrderResource;

class ViewWorkOrder extends ViewRecord
{
    use \Filament\Resources\Pages\Concerns\InteractsWithParentRecord;

    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
