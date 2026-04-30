<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Project\Models\Project;

class ProjectTimelineWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'projectTimelineChart';

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Project Timeline Overview';

    protected static ?int $contentHeight = 400;

    protected static ?int $sort = 5;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('project.timeline', function () {
            $projects = Project::where('status', '!=', 'cancelled')
                ->orderBy('start_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->take(20)
                ->get();

            $series = [];
            foreach ($projects as $project) {
                $start = $project->start_date ?? $project->created_at;
                $end = $project->end_date ?? $start->copy()->addMonth();

                $startDate = Carbon::parse($start)->timestamp * 1000;
                $endDate = Carbon::parse($end)->timestamp * 1000;

                $series[] = [
                    'name' => $project->number ?? $project->name,
                    'data' => [
                        [
                            'x' => $project->status?->getLabel() ?? 'Unknown',
                            'y' => [$startDate, $endDate],
                        ],
                    ],
                ];
            }

            if (empty($series)) {
                return [
                    [
                        'name' => 'No Projects',
                        'data' => [
                            [
                                'x' => 'No Data',
                                'y' => [0, 0],
                            ],
                        ],
                    ],
                ];
            }

            return $series;
        });

        return [
            'chart' => [
                'type' => 'rangeBar',
                'height' => 400,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => $data,
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'barHeight' => '50%',
                    'rangeBarGroupRows' => false,
                ],
            ],
            'colors' => [
                '#6366f1',
                '#10b981',
                '#f59e0b',
                '#ef4444',
                '#8b5cf6',
                '#ec4899',
                '#14b8a6',
                '#f97316',
            ],
            'fill' => [
                'type' => 'solid',
            ],
            'xaxis' => [
                'type' => 'datetime',
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                    'format' => 'MMM yyyy',
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'x' => [
                    'format' => 'dd MMM yyyy',
                ],
            ],
            'legend' => [
                'position' => 'right',
                'fontFamily' => 'inherit',
                'labels' => [
                    'useSeriesColors' => true,
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => null,
                'style' => [
                    'colors' => ['#f3f4f6'],
                ],
            ],
        ];
    }
}
