<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use BackedEnum;
use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\ProjectResource;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits\HasWorkCompletionReportActions;

class EditWorkCompletionReport extends EditRecord
{
    use HasWorkCompletionReportActions;
    use InteractsWithParentRecord;

    protected static string $resource = WorkCompletionReportResource::class;

    public static function getNavigationIcon(): BackedEnum|string|null
    {
        return Heroicon::OutlinedPencilSquare;
    }

    public function getBreadcrumbs(): array
    {
        $project = $this->getParentRecord();
        $record = $this->getRecord();

        return [
            ProjectResource::getUrl('index') => 'Projects',
            ProjectResource::getUrl('view', ['record' => $project]) => $project->name ?? 'Project',
            WorkCompletionReportResource::getUrl('index', ['project' => $project]) => 'BAPP',
            '#' => $record->number ?? 'Edit',
        ];
    }

    protected function getHeaderActions(): array
    {
        return $this->getWorkCompletionReportHeaderActions();
    }
}
