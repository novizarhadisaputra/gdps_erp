<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;

class MonthlyRevenueTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyRevenueTrendChart';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    protected static ?string $heading = 'Monthly Revenue Performance (RoFo vs Actual)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $categories = [];
        $revenueData = [];
        $costData = [];

        $currentYear = Carbon::now()->year;

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($currentYear, $m, 1);
            $monthName = $date->format('F');

            $categories[] = $date->format('M'); // Jan, Feb, etc.

            $monthlyStats = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->where('month', $monthName)
                ->selectRaw('SUM(actual_revenue) as revenue, SUM(actual_cost) as cost')
                ->first();

            $revenueData[] = round(($monthlyStats->revenue ?? 0) / 1000000, 2);
            // Cost is negative to show downwards
            $costData[] = -round(($monthlyStats->cost ?? 0) / 1000000, 2);
        }

        $data = compact('categories', 'revenueData', 'costData');

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => false,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '60%',
                    'borderRadius' => 2,
                    'colors' => [
                        'ranges' => [
                            [
                                'from' => -1000000,
                                'to' => 0,
                                'color' => '#f97316' // Orange for cost
                            ],
                            [
                                'from' => 0,
                                'to' => 1000000,
                                'color' => '#fbbf24' // Yellow for revenue
                            ]
                        ]
                    ]
                ],
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $data['revenueData'],
                ],
                [
                    'name' => 'Cost',
                    'data' => $data['costData'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['categories'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Value (Million IDR)',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],

            'tooltip' => [
                'y' => [
                    'formatter' => null,
                ],
            ],
            'colors' => ['#fbbf24', '#f97316'],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'grid' => [
                'borderColor' => '#f1f1f1',
            ],
        ];
    }

    protected function getFooter(): string|Htmlable|View|null
    {
        return new HtmlString('<p class="text-xs text-gray-500 mt-2">Comparison of monthly Rolling Forecast (RoFo) against Actual Revenue realization.</p>');
    }
}
