<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class CreateProjectChangeRequest extends CreateRecord
{
    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
