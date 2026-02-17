<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ApiClients\ApiClientResource;

class ListApiClients extends ListRecords
{
    protected static string $resource = ApiClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
