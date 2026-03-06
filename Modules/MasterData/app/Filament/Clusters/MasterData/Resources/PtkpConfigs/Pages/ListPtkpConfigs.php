<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\PtkpConfigs\PtkpConfigResource;

class ListPtkpConfigs extends ListRecords
{
    protected static string $resource = PtkpConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
