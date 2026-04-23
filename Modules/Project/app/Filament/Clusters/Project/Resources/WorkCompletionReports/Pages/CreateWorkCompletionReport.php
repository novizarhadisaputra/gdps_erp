<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;

class CreateWorkCompletionReport extends CreateRecord
{
    protected static string $resource = WorkCompletionReportResource::class;
}
