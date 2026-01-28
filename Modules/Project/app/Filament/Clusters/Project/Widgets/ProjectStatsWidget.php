<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Project\Models\Project;

class ProjectStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Projects', Project::count())
                ->description('Total project records')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('Active Projects', Project::where('status', 'active')->count())
                ->description('Projects currently in progress')
                ->descriptionIcon('heroicon-m-play')
                ->color('success'),
            Stat::make('Planning Projects', Project::where('status', 'planning')->count())
                ->description('Projects in planning phase')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),
        ];
    }
}
