<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\ProjectChangeRequests\ProjectChangeRequestResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Traits\HasProjectChangeRequestActions;

class EditProjectChangeRequest extends EditRecord
{
    use HasProjectChangeRequestActions;

    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getProjectChangeRequestHeaderActions();
    }
}
