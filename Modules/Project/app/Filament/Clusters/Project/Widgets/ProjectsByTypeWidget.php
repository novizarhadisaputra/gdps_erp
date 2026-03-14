<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Project\Models\Project;

class ProjectsByTypeWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'projectsByTypeChart';

    protected static ?string $heading = 'Projects by Type & Area';

    protected static ?int $contentHeight = 300;

    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('project.by_type', function () {
            $byType = Project::with('projectType:id,name')
                ->get()
                ->groupBy('project_type_id')
                ->map(function ($projects, $typeId) {
                    $type = $projects->first()->projectType;

                    return [
                        'name' => $type?->name ?? 'Uncategorized',
                        'count' => $projects->count(),
                        'value' => $projects->sum('amount'),
                    ];
                })
                ->values()
                ->sortByDesc('count')
                ->take(10)
                ->toArray();

            return $byType;
        });

        if (empty($data)) {
            $data = [
                ['name' => 'No Data', 'count' => 0, 'value' => 0],
            ];
        }

        $labels = array_column($data, 'name');
        $counts = array_column($data, 'count');
        $values = array_map(fn ($v) => round($v / 1000000, 2), array_column($data, 'value'));

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $counts,
            'labels' => $labels,
            'colors' => [
                '#6366f1',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
                '#ec4899',
                '#14b8a6',
                '#f97316',
                '#06b6d4',
                '#84cc16',
            ],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
                'labels' => [
                    'colors' => '#6b7280',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                            ],
                            'value' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                                'formatter' => null,
                            ],
                            'total' => [
                                'show' => true,
                                'label' => 'Total Projects',
                                'fontFamily' => 'inherit',
                                'formatter' => null,
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => null,
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
