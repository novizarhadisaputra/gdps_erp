<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmTypes\JkmTypeResource;

class ListJkmTypes extends ListRecords
{
    protected static string $resource = JkmTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
