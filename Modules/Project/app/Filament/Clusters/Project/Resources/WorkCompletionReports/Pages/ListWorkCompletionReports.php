<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\ListRecords;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;

class ListWorkCompletionReports extends ListRecords
{
    protected static string $resource = WorkCompletionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
