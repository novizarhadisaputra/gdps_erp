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

        return [
            ProjectResource::getUrl('index') => 'Projects',
            ProjectResource::getUrl('view', ['record' => $project]) => $project->name ?? 'Project',
            ProjectChangeRequestResource::getUrl('index', ['project' => $project]) => 'Change Requests',
        ];
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
