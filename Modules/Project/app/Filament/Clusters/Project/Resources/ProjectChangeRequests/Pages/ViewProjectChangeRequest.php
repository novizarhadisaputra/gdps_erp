<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\ProjectChangeRequestResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Traits\HasProjectChangeRequestActions;

class ViewProjectChangeRequest extends ViewRecord
{
    use HasProjectChangeRequestActions;

    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getProjectChangeRequestHeaderActions();
    }
}
