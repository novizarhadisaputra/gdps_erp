<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Traits\HasProjectChangeRequestActions;

class EditProjectChangeRequest extends EditRecord
{
    use HasProjectChangeRequestActions;
    use InteractsWithParentRecord;

    protected static string $resource = ProjectChangeRequestResource::class;

    public function getBreadcrumbs(): array
    {
        $project = $this->getParentRecord();
        $record = $this->getRecord();

        return [
            ProjectResource::getUrl('index') => 'Projects',
            ProjectResource::getUrl('view', ['record' => $project]) => $project->name ?? 'Project',
            ProjectChangeRequestResource::getUrl('index', ['project' => $project]) => 'Change Requests',
            '#' => $record->number ?? 'Edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->getProjectChangeRequestHeaderActions();
    }
}
