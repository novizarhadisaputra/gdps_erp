<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\Pages;

use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\DailyReports\DailyReportResource;

class ManageDailyReportComments extends Page
{
    use InteractsWithRecord;

    protected static string $resource = DailyReportResource::class;

    protected static ?string $title = 'Report Discussions';

    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'project::filament.clusters.project.resources.daily-reports.pages.manage-daily-report-comments';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }
}
