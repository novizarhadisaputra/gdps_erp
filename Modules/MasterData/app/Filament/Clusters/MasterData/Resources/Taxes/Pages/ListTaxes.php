<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Taxes\TaxResource;

class ListTaxes extends ListRecords
{
    protected static string $resource = TaxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
