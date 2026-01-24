<?php

namespace Modules\MasterData\Filament\Resources\UnitsOfMeasure\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\UnitOfMeasureResource;

class ListUnitsOfMeasure extends ListRecords
{
    protected static string $resource = UnitOfMeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
