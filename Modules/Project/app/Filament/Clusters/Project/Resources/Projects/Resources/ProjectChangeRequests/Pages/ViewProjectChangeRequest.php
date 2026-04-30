<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class ViewProjectChangeRequest extends ViewRecord
{
    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
