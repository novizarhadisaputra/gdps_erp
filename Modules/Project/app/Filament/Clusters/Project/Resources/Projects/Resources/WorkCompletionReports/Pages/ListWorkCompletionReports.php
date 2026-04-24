<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\WorkCompletionReport;

class ListWorkCompletionReports extends ListRecords
{
    protected static string $resource = WorkCompletionReportResource::class;

    public function getTabs(): array
    {
        $projectId = request()->route('project');

        return [
            'all' => Tab::make(__('project::work_completion_report.tabs.all')),
            'draft' => Tab::make(__('project::work_completion_report.tabs.draft'))
                ->badge(fn() => WorkCompletionReport::query()
                    ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                    ->where('status', WorkCompletionStatus::Draft)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Draft)),
            'sent' => Tab::make(__('project::work_completion_report.tabs.sent'))
                ->badge(fn() => WorkCompletionReport::query()
                    ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                    ->where('status', WorkCompletionStatus::Sent)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Sent)),
            'approved' => Tab::make(__('project::work_completion_report.tabs.approved'))
                ->badge(fn() => WorkCompletionReport::query()
                    ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                    ->where('status', WorkCompletionStatus::Approved)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Approved)),
            'rejected' => Tab::make(__('project::work_completion_report.tabs.rejected'))
                ->badge(fn() => WorkCompletionReport::query()
                    ->when($projectId, fn($q) => $q->where('project_id', $projectId))
                    ->where('status', WorkCompletionStatus::Rejected)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Rejected)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
