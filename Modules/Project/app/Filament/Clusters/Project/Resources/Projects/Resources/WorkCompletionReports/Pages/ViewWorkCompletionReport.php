<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits\HasWorkCompletionReportActions;

class ViewWorkCompletionReport extends ViewRecord
{
    use HasWorkCompletionReportActions;
    use InteractsWithParentRecord;

    protected static string $resource = WorkCompletionReportResource::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedEye;
    }

    public function getBreadcrumbs(): array
    {
        $project = $this->getParentRecord();
        $record = $this->getRecord();

        $breadcrumbs = [
            ProjectResource::getUrl('index') => 'Projects',
        ];

        if ($project) {
            $breadcrumbs[ProjectResource::getUrl('view', ['record' => $project])] = $project->name ?? 'Project';
            $breadcrumbs[WorkCompletionReportResource::getUrl('index', ['project' => $project])] = 'BAPP';
        }

        $breadcrumbs['#'] = $record->number ?? 'View';

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        return $this->getWorkCompletionReportHeaderActions();
    }
}
