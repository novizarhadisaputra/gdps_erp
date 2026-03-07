<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\HealthConfigResource;

class ListHealthConfigs extends ListRecords
{
    protected static string $resource = HealthConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Manage Health Insurance (BPJS Kesehatan) configurations.';
    }
}
