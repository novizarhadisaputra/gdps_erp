<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class CreateProjectChangeRequest extends CreateRecord
{
    protected static string $resource = ProjectChangeRequestResource::class;
}
