<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Traits\HasWorkCompletionReportActions;

class EditWorkCompletionReport extends EditRecord
{
    use HasWorkCompletionReportActions;

    protected static string $resource = WorkCompletionReportResource::class;

    protected function getHeaderActions(): array
    {
        return $this->getWorkCompletionReportHeaderActions();
    }
}
