<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Items\ItemResource;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            Actions\CreateAction::make(),
        ];
    }
}
