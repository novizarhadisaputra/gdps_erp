<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\UnitsOfMeasure\UnitOfMeasureResource;

class ListUnitsOfMeasure extends ListRecords
{
    protected static string $resource = UnitOfMeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}
