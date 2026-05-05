<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class CreateProjectChangeRequest extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = ProjectChangeRequestResource::class;

    public function getBreadcrumbs(): array
    {
        $project = $this->getParentRecord();

        return [
            ProjectResource::getUrl('index') => 'Projects',
            ProjectResource::getUrl('view', ['record' => $project]) => $project->name ?? 'Project',
            ProjectChangeRequestResource::getUrl('index', ['project' => $project]) => 'Change Requests',
            '#' => 'Create',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
