<?php

namespace Modules\Project\Filament\Resources\ProjectInformations\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Resources\ProjectInformations\ProjectInformationResource;

class ListProjectInformations extends ListRecords
{
    protected static string $resource = ProjectInformationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
