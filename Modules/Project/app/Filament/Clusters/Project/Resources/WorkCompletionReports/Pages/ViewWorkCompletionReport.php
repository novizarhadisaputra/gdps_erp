<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\ViewRecord;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits\HasWorkCompletionReportActions;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;

class ViewWorkCompletionReport extends ViewRecord
{
    use HasWorkCompletionReportActions;

    protected static string $resource = WorkCompletionReportResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getWorkCompletionReportHeaderActions();
    }
}
