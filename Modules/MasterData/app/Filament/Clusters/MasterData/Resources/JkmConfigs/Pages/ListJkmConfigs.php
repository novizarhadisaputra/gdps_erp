<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\JkmConfigResource;

class ListJkmConfigs extends ListRecords
{
    protected static string $resource = JkmConfigResource::class;

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
