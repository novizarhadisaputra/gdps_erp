<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\ProductClusterResource;

class CreateProductCluster extends CreateRecord
{
    protected static string $resource = ProductClusterResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
