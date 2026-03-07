<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkConfigs\JkkConfigResource;

class ListJkkConfigs extends ListRecords
{
    protected static string $resource = JkkConfigResource::class;

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
