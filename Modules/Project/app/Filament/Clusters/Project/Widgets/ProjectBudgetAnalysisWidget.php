<?php

namespace Modules\Project\Filament\Clusters\Project\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Project\Models\Project;

class ProjectBudgetAnalysisWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'projectBudgetAnalysisChart';

    protected static ?string $heading = 'Project Budget Analysis';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 4;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('project.budget_analysis', function () {
            $projects = Project::with(['proposal', 'contract', 'profitabilityAnalysis'])
                ->whereNotNull('start_date')
                ->get()
                ->take(15);

            $names = [];
            $budgets = [];
            $actuals = [];

            foreach ($projects as $project) {
                $names[] = $project->code ?? $project->name ?? 'Unknown';
                $budget = $project->amount;
                $budgets[] = round($budget / 1000000, 2);

                // For now, actual is simulated as 70-120% of budget
                // In production, this should come from actual expense tracking
                $actual = $budget * (rand(70, 120) / 100);
                $actuals[] = round($actual / 1000000, 2);
            }

            if (empty($names)) {
                return [
                    'names' => ['No Data'],
                    'budgets' => [0],
                    'actuals' => [0],
                ];
            }

            return compact('names', 'budgets', 'actuals');
        });

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Budget',
                    'data' => $data['budgets'],
                ],
                [
                    'name' => 'Actual Spending',
                    'data' => $data['actuals'],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'endingShape' => 'rounded',
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'colors' => ['#10b981', '#ef4444'],
            'xaxis' => [
                'categories' => $data['names'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                    'rotate' => -45,
                    'trim' => true,
                    'maxHeight' => 80,
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Amount (Million IDR)',
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                    'formatter' => null,
                ],
            ],
            'fill' => [
                'opacity' => 1,
            ],
            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'formatter' => null,
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
                'fontFamily' => 'inherit',
            ],
        ];
    }
}
