<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkmConfigs\BpjsJkmConfigResource;

class ListBpjsJkmConfigs extends ListRecords
{
    protected static string $resource = BpjsJkmConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Manage Life Insurance (JKM) configurations.';
    }
}
