<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Models\SalesPlanMonthly;

class SalesPerformanceChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'salesPerformanceChart';

    protected static ?string $heading = 'Revenue Performance (Cumulative)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 5;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('sales_performance_cumulative', function () {
            $year = now()->year;
            $months = [];
            $budgetData = [];
            $forecastData = [];
            $actualData = [];

            $cumulativeBudget = 0;
            $cumulativeForecast = 0;
            $cumulativeActual = 0;

            for ($m = 1; $m <= 12; $m++) {
                $date = Carbon::create($year, $m, 1)->startOfDay();
                $months[] = $date->format('M');

                $totals = SalesPlanMonthly::query()
                    ->where('year', $year)
                    ->where('month', $m)
                    ->selectRaw('SUM(budget_amount) as budget, SUM(forecast_amount) as forecast, SUM(actual_amount) as actual')
                    ->first();

                $cumulativeBudget += (float) ($totals->budget ?? 0);
                $cumulativeForecast += (float) ($totals->forecast ?? 0);
                $cumulativeActual += (float) ($totals->actual ?? 0);

                $budgetData[] = round($cumulativeBudget / 1000000, 2);
                $forecastData[] = round($cumulativeForecast / 1000000, 2);

                // For actual data, show only up to current month
                if ($date->lte(now()->startOfMonth())) {
                    $actualData[] = round($cumulativeActual / 1000000, 2);
                } else {
                    $actualData[] = null;
                }
            }

            return compact('months', 'budgetData', 'forecastData', 'actualData');
        });

        return [
            'chart' => [
                'type' => 'line',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'RKAP Sales Plan (Target)',
                    'data' => $data['budgetData'],
                ],
                [
                    'name' => 'Rolling Forecast (RoFo)',
                    'data' => $data['forecastData'],
                ],
                [
                    'name' => 'Accrue Sales (Actual)',
                    'data' => $data['actualData'],
                ],
            ],
            'colors' => ['#3b82f6', '#ef4444', '#f59e0b'], // Blue, Red, Yellow/Orange
            'stroke' => [
                'curve' => 'smooth',
                'width' => 3,
            ],
            'markers' => [
                'size' => 4,
            ],
            'xaxis' => [
                'categories' => $data['months'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Billion IDR',
                ],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return val + " M" }',
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
            ],
        ];
    }
}
