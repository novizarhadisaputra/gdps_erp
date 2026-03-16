<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Resources\Amendment\AmendmentResource;

class ListAmendments extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = AmendmentResource::class;

    public function getSubheading(): ?string
    {
        return 'View previous amendments of this sales order.';
    }
}
