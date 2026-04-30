<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueSegmentDonutWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueSegmentDonut';

    protected static ?string $heading = 'Target Revenue per Segment';

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getRevenueBySegment();

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $data['data'],
            'labels' => $data['labels'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'colors' => ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            'tooltip' => [
                'theme' => 'dark',
            ],
        ];
    }
}
