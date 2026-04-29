<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Models\SalesPlanMonthly;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;

class SalesPerformanceChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'salesPerformanceChart';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    protected static ?string $heading = 'Revenue Performance (Cumulative)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 5;

    protected function getOptions(): array
    {
        $year = now()->year;
        $months = [];
        $budgetData = [];
        $forecastData = [];
        $actualData = [];

        $monthlyMetrics = SalesPlanMonthly::query()
            ->where('year', $year)
            ->get()
            ->groupBy('month');

        $cumulativeBudget = 0;
        $cumulativeForecast = 0;
        $cumulativeActual = 0;

        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create($year, $m, 1);
            $monthName = $date->format('F');
            $months[] = $date->format('M');

            $monthData = $monthlyMetrics->get($m);

            $monthlyBudget = $monthData ? $monthData->sum('budget_amount') : 0;
            $monthlyForecast = $monthData ? $monthData->sum('forecast_amount') : 0;
            $monthlyActual = $monthData ? $monthData->sum('actual_amount') : 0;

            $cumulativeBudget += (float) $monthlyBudget;
            $cumulativeForecast += (float) $monthlyForecast;
            $cumulativeActual += (float) $monthlyActual;

            $budgetData[] = round($cumulativeBudget / 1000000, 2); // Million IDR
            $forecastData[] = round($cumulativeForecast / 1000000, 2);

            if ($date->lte(now()->startOfMonth())) {
                $actualData[] = round($cumulativeActual / 1000000, 2);
            } else {
                $actualData[] = null;
            }
        }

        $data = compact('months', 'budgetData', 'forecastData', 'actualData');

        return [
            'chart' => [
                'type' => 'area',
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
            'colors' => ['#4f46e5', '#ef4444', '#10b981'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'markers' => [
                'size' => 4,
            ],
            'xaxis' => [
                'categories' => $data['months'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Million IDR',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'tooltip' => [
                'enabled' => true,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [50, 100],
                ],
            ],
        ];
    }

    protected function getFooter(): string|Htmlable|View|null
    {
        return new HtmlString('<p class="text-xs text-gray-500 mt-2">Year-to-date (YTD) tracking of revenue performance compared to annual Sales Plan (RKAP).</p>');
    }
}
