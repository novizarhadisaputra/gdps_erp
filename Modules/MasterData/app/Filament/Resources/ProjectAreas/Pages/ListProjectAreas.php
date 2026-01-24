<?php

namespace Modules\MasterData\Filament\Resources\ProjectAreas\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\MasterData\Filament\Resources\ProjectAreas\ProjectAreaResource;

class ListProjectAreas extends ListRecords
{
    protected static string $resource = ProjectAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
