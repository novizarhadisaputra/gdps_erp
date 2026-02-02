<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\AssetGroups\AssetGroupResource;

class ListAssetGroups extends ListRecords
{
    protected static string $resource = AssetGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
