<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\JhtConfigResource;

class ListJhtConfigs extends ListRecords
{
    protected static string $resource = JhtConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Manage Old Age Security (JHT) configurations.';
    }
}
