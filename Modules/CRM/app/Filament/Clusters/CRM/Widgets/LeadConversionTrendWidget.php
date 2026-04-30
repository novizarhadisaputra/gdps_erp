<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Locked;

class LeadConversionTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'leadConversionTrendChart';

    #[Locked]
    public ?array $options = null;

    protected static ?string $heading = 'Lead Conversion Trend';

    protected static ?int $contentHeight = 300;

    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getLeadConversionTrend();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Won',
                    'type' => 'column',
                    'data' => $data['wonData'],
                ],
                [
                    'name' => 'Lost',
                    'type' => 'column',
                    'data' => $data['lostData'],
                ],
                [
                    'name' => 'Conversion Rate (%)',
                    'type' => 'line',
                    'data' => $data['conversionRateData'],
                ],
            ],
            'stroke' => [
                'width' => [0, 0, 3],
                'curve' => 'smooth',
            ],
            'plotOptions' => [
                'bar' => [
                    'columnWidth' => '50%',
                ],
            ],
            'fill' => [
                'opacity' => [0.85, 0.85, 1],
                'gradient' => [
                    'inverseColors' => false,
                    'shade' => 'light',
                    'type' => 'vertical',
                    'opacityFrom' => 0.85,
                    'opacityTo' => 0.55,
                    'stops' => [0, 100, 100, 100],
                ],
            ],
            'colors' => ['#10b981', '#ef4444', '#6366f1'],
            'labels' => $data['months'],
            'markers' => [
                'size' => [0, 0, 5],
            ],
            'xaxis' => [
                'type' => 'category',
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                [
                    'title' => [
                        'text' => 'Number of Leads',
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
                [
                    'opposite' => true,
                    'title' => [
                        'text' => 'Conversion Rate (%)',
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'shared' => true,
                'intersect' => false,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
                'fontFamily' => 'inherit',
            ],
        ];
    }
}
