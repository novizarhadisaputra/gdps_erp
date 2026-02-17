<?php

namespace Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesPlan\SalesPlanResource;

class ListSalesPlans extends ListRecords
{
    protected static string $resource = SalesPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
