<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Resources\ProductClusters\ProductClusterResource;

class ListProductClusters extends ListRecords
{
    protected static string $resource = ProductClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
