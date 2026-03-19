<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Support\Icons\Heroicon;
use Modules\Project\Models\Project;

class ProjectStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $cache = app(AnalyticsCacheService::class);

        return $cache->rememberRealtime('project.stats_overview', function () {
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', 'active')->count();
            $planningProjects = Project::where('status', 'planning')->count();
            $completedProjects = Project::where('status', 'completed')->count();

            // Calculate success rate
            $successRate = $totalProjects > 0
                ? round(($completedProjects / $totalProjects) * 100, 1)
                : 0;

            // Total project value
            $totalValue = Project::with('proposal')->get()->sum(function ($project) {
                return $project->amount;
            });

            // Overdue projects (active projects past end_date)
            $overdueProjects = Project::where('status', 'active')
                ->where('end_date', '<', Carbon::today())
                ->count();

            // Average project duration
            $avgDuration = Project::whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->get()
                ->avg(function ($project) {
                    if ($project->start_date && $project->end_date) {
                        return Carbon::parse($project->start_date)->diffInDays(Carbon::parse($project->end_date));
                    }

                    return 0;
                });

            return [
                Stat::make('Total Projects', number_format($totalProjects))
                    ->description('All project records')
                    ->descriptionIcon(Heroicon::Briefcase)
                    ->color('primary')
                    ->chart($this->getProjectSparklineData()),

                Stat::make('Active Projects', number_format($activeProjects))
                    ->description('Currently in progress')
                    ->descriptionIcon(Heroicon::Play)
                    ->color('success'),

                Stat::make('Planning Projects', number_format($planningProjects))
                    ->description('In planning phase')
                    ->descriptionIcon(Heroicon::ClipboardDocumentList)
                    ->color('warning'),

                Stat::make('Completed Projects', number_format($completedProjects))
                    ->description($successRate.'% success rate')
                    ->descriptionIcon(Heroicon::CheckCircle)
                    ->color('info'),

                Stat::make('Total Value', 'Rp '.number_format($totalValue / 1000000, 0, ',', '.').'M')
                    ->description('All projects value')
                    ->descriptionIcon(Heroicon::CurrencyDollar)
                    ->color('primary'),

                Stat::make('Overdue Projects', number_format($overdueProjects))
                    ->description('Past end date')
                    ->descriptionIcon(Heroicon::ExclamationTriangle)
                    ->color($overdueProjects > 0 ? 'danger' : 'success'),

                Stat::make('Avg Duration', round($avgDuration ?? 0).' days')
                    ->description('Average project length')
                    ->descriptionIcon(Heroicon::Clock)
                    ->color('gray'),
            ];
        });
    }

    protected function getProjectSparklineData(): array
    {
        $days = 7;
        $data = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Project::whereDate('created_at', '<=', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}
