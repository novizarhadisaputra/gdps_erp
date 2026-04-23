<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class MonthlyRevenueTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyRevenueTrendChart';

    protected static ?string $heading = 'Monthly Revenue Performance (RoFo vs Actual)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('crm.so_monthly_revenue_trend', function () {
            $categories = [];
            $targetData = [];
            $actualData = [];
            $grossProfitData = [];

            $currentYear = Carbon::now()->year;

            for ($m = 1; $m <= 12; $m++) {
                $date = Carbon::create()->year($currentYear)->month($m);
                $monthName = $date->format('F');

                $categories[] = $date->format('M Y');

                $monthlyStats = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                    ->where('month', $monthName)
                    ->selectRaw('SUM(target_revenue) as target, SUM(actual_revenue) as actual, SUM(gross_profit) as gp')
                    ->first();

                $targetData[] = round(($monthlyStats->target ?? 0) / 1000000, 2);
                $actualData[] = round(($monthlyStats->actual ?? 0) / 1000000, 2);
                $grossProfitData[] = round(($monthlyStats->gp ?? 0) / 1000000, 2);
            }

            return compact('categories', 'targetData', 'actualData', 'grossProfitData');
        });

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => false,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'borderRadius' => 4,
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
            'series' => [
                [
                    'name' => 'Target RoFo',
                    'data' => $data['targetData'],
                ],
                [
                    'name' => 'Actual Revenue',
                    'data' => $data['actualData'],
                ],
                [
                    'name' => 'Gross Profit',
                    'data' => $data['grossProfitData'],
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
                    'formatter' => null,
                ],
            ],
            'fill' => [
                'opacity' => 1,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => null,
                ],
            ],
            'colors' => ['#6366f1', '#10b981', '#f59e0b'],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'grid' => [
                'borderColor' => '#f1f1f1',
            ],
        ];
    }
}
