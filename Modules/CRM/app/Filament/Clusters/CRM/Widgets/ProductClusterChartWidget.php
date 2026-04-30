<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProductClusterChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'productClusterChart';

    protected static ?string $heading = 'Revenue per Product Cluster';

    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getRevenueByProductCluster();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'borderRadius' => 4,
                ],
            ],
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $data['data'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['labels'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#10b981'],
            'tooltip' => [
                'theme' => 'dark',
            ],
        ];
    }
}
