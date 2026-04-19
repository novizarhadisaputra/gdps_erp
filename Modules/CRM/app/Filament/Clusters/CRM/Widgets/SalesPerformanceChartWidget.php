<?php
 
namespace Modules\CRM\Filament\Clusters\CRM\Widgets;
 
use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Illuminate\Support\Facades\DB;
 
class SalesPerformanceChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'salesPerformanceChart';
 
    protected static ?string $heading = 'Revenue Performance (Cumulative)';
 
    protected static ?int $contentHeight = 350;
 
    protected static ?int $sort = 5;
 
    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);
 
        $data = $cache->rememberHourly('crm.sales_performance_cumulative', function () {
            $year = now()->year;
            $months = [];
            $budgetData = [];
            $forecastData = [];
            $actualData = [];
 
            $monthlyMetrics = \Modules\Finance\Models\ProfitabilityAnalysisMonthly::query()
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
 
                $monthData = $monthlyMetrics->get($monthName);
                
                $monthlyBudget = $monthData ? $monthData->sum('target_revenue') : 0;
                $monthlyForecast = $monthData ? $monthData->sum('forecast_revenue') : 0;
                $monthlyActual = $monthData ? $monthData->sum('actual_revenue') : 0;
 
                $cumulativeBudget += (float) $monthlyBudget;
                $cumulativeForecast += (float) $monthlyForecast;
                $cumulativeActual += (float) $monthlyActual;
 
                $budgetData[] = round($cumulativeBudget / 1000000000, 3); // Billion IDR
                $forecastData[] = round($cumulativeForecast / 1000000000, 3);
 
                if ($date->lte(now()->startOfMonth())) {
                    $actualData[] = round($cumulativeActual / 1000000000, 3);
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
            'colors' => ['#3b82f6', '#ef4444', '#10b981'], // Blue, Red, Green
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
                'labels' => [
                    'formatter' => 'function (val) { return val.toFixed(1) + " B" }',
                ],
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) { return val.toFixed(3) + " B" }',
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
            ],
        ];
    }
}
