<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;

class CreateAccrueRevenue extends CreateRecord
{
    protected static string $resource = AccrueRevenueResource::class;
}
