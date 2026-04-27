<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;

class ViewAccrueRevenue extends ViewRecord
{
    protected static string $resource = AccrueRevenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
