<?php

namespace Modules\MasterData\Filament\Resources\ProductClusters\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Resources\ProductClusters\ProductClusterResource;

class EditProductCluster extends EditRecord
{
    protected static string $resource = ProductClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
