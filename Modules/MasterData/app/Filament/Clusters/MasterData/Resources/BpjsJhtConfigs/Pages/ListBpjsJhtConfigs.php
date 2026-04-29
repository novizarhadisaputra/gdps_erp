<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\BpjsJhtConfigResource;

class ListBpjsJhtConfigs extends ListRecords
{
    protected static string $resource = BpjsJhtConfigResource::class;

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
