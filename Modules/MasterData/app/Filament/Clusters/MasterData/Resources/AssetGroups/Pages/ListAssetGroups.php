<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Pages;

use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\AssetGroupResource;

class ListAssetGroups extends ListRecords
{
    protected static string $resource = AssetGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ExcelImportAction::make()
                ->color('primary'),
            CreateAction::make(),
        ];
    }
}
