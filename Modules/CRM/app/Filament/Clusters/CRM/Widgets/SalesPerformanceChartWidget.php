<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityAnalysisWeekly;
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

            $cumulativeBudget = 0;
            $cumulativeForecast = 0;
            $cumulativeActual = 0;

            for ($m = 1; $m <= 12; $m++) {
                $monthStart = Carbon::create($year, $m, 1)->startOfDay();
                $monthEnd = $monthStart->copy()->endOfMonth();
                $months[] = $monthStart->format('M');

                // 1. Target (RKAP) - From PA revenue_per_month where active in this month
                $monthlyBudget = ProfitabilityAnalysis::query()
                    ->where(function ($query) use ($monthStart, $monthEnd) {
                        $query->where('start_date', '<=', $monthEnd)
                            ->where('end_date', '>=', $monthStart);
                    })
                    ->sum('revenue_per_month');

                // 2. Forecast (RoFo) - Latest projections for this month
                $latestForecastIds = ProfitabilityAnalysisWeekly::query()
                    ->select('id')
                    ->where('year', $year)
                    ->where('month', $monthStart->format('F'))
                    ->whereIn('created_at', function ($query) use ($year, $monthStart) {
                        $query->select(DB::raw('MAX(finance.profitability_analysis_weeklies.created_at)'))
                            ->from('finance.profitability_analysis_weeklies')
                            ->join('finance.profitability_analysis_monthlies', 'finance.profitability_analysis_weeklies.profitability_analysis_monthly_id', '=', 'finance.profitability_analysis_monthlies.id')
                            ->where('finance.profitability_analysis_weeklies.year', $year)
                            ->where('finance.profitability_analysis_weeklies.month', $monthStart->format('F'))
                            ->groupBy('finance.profitability_analysis_monthlies.profitability_analysis_id');
                    })
                    ->pluck('id');

                $monthlyForecast = ProfitabilityAnalysisWeekly::whereIn('id', $latestForecastIds)->sum('projected_revenue');

                // 3. Actual - From Realized Monthly data
                $monthlyActual = ProfitabilityAnalysisMonthly::query()
                    ->where('year', $year)
                    ->where('month', $monthStart->format('F'))
                    ->sum('actual_revenue');

                $cumulativeBudget += (float) $monthlyBudget;
                $cumulativeForecast += (float) $monthlyForecast;
                $cumulativeActual += (float) $monthlyActual;

                $budgetData[] = round($cumulativeBudget / 1000000000, 3); // Billion IDR
                $forecastData[] = round($cumulativeForecast / 1000000000, 3);

                if ($monthStart->lte(now()->startOfMonth())) {
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
