<?php

namespace Modules\MasterData\Filament\Resources\UnitsOfMeasure\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\UnitOfMeasureResource;

class EditUnitOfMeasure extends EditRecord
{
    protected static string $resource = UnitOfMeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
