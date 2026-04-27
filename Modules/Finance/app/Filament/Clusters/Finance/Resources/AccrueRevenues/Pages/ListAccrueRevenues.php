<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\AccrueRevenueResource;

class ListAccrueRevenues extends ListRecords
{
    protected static string $resource = AccrueRevenueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
