<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Locked;

class LeadPipelineWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'leadPipelineChart';

    #[Locked]
    public ?array $options = null;

    protected static ?string $heading = 'Sales Pipeline';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getLeadPipelineData();

        $stages = ['Prospecting (Level 1)', 'Proposal (Level 2)', 'Negotiation (Level 3)', 'Finalization (Level 4)'];
        $counts = [
            $data['level_1_count'],
            $data['level_2_count'],
            $data['level_3_count'],
            $data['level_4_count'],
        ];

        $values = [
            $data['level_1_value'],
            $data['level_2_value'],
            $data['level_3_value'],
            $data['level_4_value'],
        ];

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                    'tools' => [
                        'download' => true,
                    ],
                ],
            ],
            'series' => [
                [
                    'name' => 'Number of Leads',
                    'data' => $counts,
                ],
                [
                    'name' => 'Total Value (Million)',
                    'data' => array_map(fn ($v) => round($v / 1000000, 2), $values),
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => true,
                    'dataLabels' => [
                        'position' => 'top',
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'offsetX' => 0,
                'style' => [
                    'fontSize' => '12px',
                    'colors' => ['#fff'],
                ],
            ],
            'colors' => ['#6366f1', '#10b981'],
            'stroke' => [
                'show' => true,
                'width' => 1,
                'colors' => ['#fff'],
            ],
            'xaxis' => [
                'categories' => $stages,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'formatter' => null,
                ],
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
                'fontFamily' => 'inherit',
            ],
        ];
    }
}
