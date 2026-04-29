<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtTypes\JhtTypeResource;

class ListJhtTypes extends ListRecords
{
    protected static string $resource = JhtTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
