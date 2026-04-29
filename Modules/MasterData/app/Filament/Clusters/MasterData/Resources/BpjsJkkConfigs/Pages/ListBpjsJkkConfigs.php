<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\BpjsJkkConfigResource;

class ListBpjsJkkConfigs extends ListRecords
{
    protected static string $resource = BpjsJkkConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Manage Work Accident Insurance (JKK) configurations.';
    }
}
