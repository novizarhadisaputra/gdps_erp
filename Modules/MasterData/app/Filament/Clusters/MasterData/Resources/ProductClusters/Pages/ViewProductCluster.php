<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\ProductClusterResource;

class ViewProductCluster extends ViewRecord
{
    protected static string $resource = ProductClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
