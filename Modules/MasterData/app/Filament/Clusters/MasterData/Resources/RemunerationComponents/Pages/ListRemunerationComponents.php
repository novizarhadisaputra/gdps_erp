<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\RemunerationComponents\RemunerationComponentResource;

class ListRemunerationComponents extends ListRecords
{
    protected static string $resource = RemunerationComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
