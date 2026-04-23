<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;

class EditWorkCompletionReport extends EditRecord
{
    protected static string $resource = WorkCompletionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
