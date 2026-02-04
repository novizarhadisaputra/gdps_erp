<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsConfigs\BpjsConfigResource;

class ListBpjsConfigs extends ListRecords
{
    protected static string $resource = BpjsConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
