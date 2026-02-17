<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RegencyMinimumWages\RegencyMinimumWageResource;

class ListRegencyMinimumWages extends ListRecords
{
    protected static string $resource = RegencyMinimumWageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
