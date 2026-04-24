<?php

namespace Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Filament\Clusters\Project\Resources\WorkCompletionReports\WorkCompletionReportResource;
use Modules\Project\Models\WorkCompletionReport;

class ListWorkCompletionReports extends ListRecords
{
    protected static string $resource = WorkCompletionReportResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'draft' => Tab::make('Draft')
                ->badge(WorkCompletionReport::query()->where('status', WorkCompletionStatus::Draft)->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Draft)),
            'sent' => Tab::make('Sent')
                ->badge(WorkCompletionReport::query()->where('status', WorkCompletionStatus::Sent)->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Sent)),
            'approved' => Tab::make('Approved')
                ->badge(WorkCompletionReport::query()->where('status', WorkCompletionStatus::Approved)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Approved)),
            'rejected' => Tab::make('Rejected')
                ->badge(WorkCompletionReport::query()->where('status', WorkCompletionStatus::Rejected)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn ($query) => $query->where('status', WorkCompletionStatus::Rejected)),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
