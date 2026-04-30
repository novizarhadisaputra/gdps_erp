<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProductClusters\ProductClusterResource;

class EditProductCluster extends EditRecord
{
    protected static string $resource = ProductClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
