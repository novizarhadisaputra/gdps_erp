<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Project\Models\Project;

class ProjectsByStatusWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'projectsByStatusChart';

    protected static ?string $heading = 'Projects by Status';

    protected static ?int $contentHeight = 300;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberRealtime('project.by_status', function () {
            $statuses = ['planning', 'active', 'completed', 'on_hold', 'cancelled'];
            $counts = [];
            $values = [];

            foreach ($statuses as $status) {
                $projects = Project::where('status', $status)->get();
                $counts[] = $projects->count();
                $values[] = $projects->sum('amount');
            }

            return [
                'labels' => ['Planning', 'Active', 'Completed', 'On Hold', 'Cancelled'],
                'counts' => $counts,
                'values' => array_map(fn ($v) => round($v / 1000000, 2), $values),
            ];
        });

        return [
            'chart' => [
                'type' => 'pie',
                'height' => 300,
            ],
            'series' => $data['counts'],
            'labels' => $data['labels'],
            'colors' => ['#94a3b8', '#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
                'labels' => [
                    'colors' => '#6b7280',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'dataLabels' => [
                        'offset' => -10,
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => null,
                'style' => [
                    'fontSize' => '14px',
                    'fontFamily' => 'inherit',
                    'fontWeight' => 'bold',
                ],
                'dropShadow' => [
                    'enabled' => false,
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'formatter' => null,
                ],
            ],
        ];
    }
}
