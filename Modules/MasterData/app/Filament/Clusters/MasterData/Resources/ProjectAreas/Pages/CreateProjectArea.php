<?php

namespace Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ProjectAreas\ProjectAreaResource;

class CreateProjectArea extends CreateRecord
{
    protected static string $resource = ProjectAreaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
