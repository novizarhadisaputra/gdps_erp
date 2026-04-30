<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class EditProjectChangeRequest extends EditRecord
{
    protected static string $resource = ProjectChangeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
