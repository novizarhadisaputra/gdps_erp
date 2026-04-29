<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpTypes\JpTypeResource;

class ListJpTypes extends ListRecords
{
    protected static string $resource = JpTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
