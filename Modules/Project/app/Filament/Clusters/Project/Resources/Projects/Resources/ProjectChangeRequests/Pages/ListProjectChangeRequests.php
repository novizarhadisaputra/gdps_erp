<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\ProjectChangeRequests\ProjectChangeRequestResource;

class ListProjectChangeRequests extends ListRecords
{
    use InteractsWithParentRecord;

    protected static string $resource = ProjectChangeRequestResource::class;

    public function getBreadcrumbs(): array
    {
        $project = $this->getParentRecord();

        $breadcrumbs = [
            ProjectResource::getUrl('index') => 'Projects',
        ];

        if ($project) {
            $breadcrumbs[ProjectResource::getUrl('view', ['record' => $project])] = $project->name ?? 'Project';
        }

        $breadcrumbs[ProjectChangeRequestResource::getUrl('index', ['project' => $project])] = 'Change Requests';

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('New Change Request')
                ->icon('heroicon-o-plus'),
        ];
    }
}
