<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsHealthConfigs\BpjsHealthConfigResource;

class ListBpjsHealthConfigs extends ListRecords
{
    protected static string $resource = BpjsHealthConfigResource::class;

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
