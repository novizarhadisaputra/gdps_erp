<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJpConfigs\BpjsJpConfigResource;

class ListBpjsJpConfigs extends ListRecords
{
    protected static string $resource = BpjsJpConfigResource::class;

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
