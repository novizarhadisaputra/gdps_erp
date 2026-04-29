<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkkTypes\JkkTypeResource;

class ListJkkTypes extends ListRecords
{
    protected static string $resource = JkkTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
