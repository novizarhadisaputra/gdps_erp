<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\JpConfigResource;

class ListJpConfigs extends ListRecords
{
    protected static string $resource = JpConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Manage Pension Security (JP) configurations.';
    }
}
