<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Filament\Support\RawJs;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyPerformanceTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyPerformanceTrend';

    protected static ?string $heading = 'Monthly Performance Trend (Actual vs Plan vs Forecast)';

    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getMonthlyPerformanceTrend();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 400,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '70%',
                    'borderRadius' => 4,
                ],
            ],
            'series' => [
                [
                    'name' => 'Actual Revenue',
                    'data' => $data['actualData'],
                ],
                [
                    'name' => 'Forecast (RoFo)',
                    'data' => $data['forecastData'],
                ],
                [
                    'name' => 'Plan (Budget)',
                    'data' => $data['targetData'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['months'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'IDR Million',
                ],
            ],
            'colors' => ['#10b981', '#f59e0b', '#6366f1'],
            'dataLabels' => [
                'enabled' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'right',
            ],
        ];
    }

    protected function extraJsOptions(): ?RawJs
    {
        return RawJs::make(<<<'JS'
            {
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: function(val) {
                            return val + ' M';
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f1f1',
                }
            }
        JS);
    }
}
