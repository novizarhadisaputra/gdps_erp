<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithParentRecord;
use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;

class CreateWorkCompletionReport extends CreateRecord
{
    use InteractsWithParentRecord;

    protected static string $resource = WorkCompletionReportResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $project = $this->parentRecord;
        if ($project) {
            $data['project_id'] = $project->id;
            $data['customer_id'] = $project->customer_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', [
            'record' => $this->getRecord(),
            'project' => $this->parentRecord,
        ]);
    }
}
