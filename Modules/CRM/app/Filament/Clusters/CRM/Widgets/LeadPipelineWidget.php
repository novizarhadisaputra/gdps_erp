<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

class LeadPipelineWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'leadPipelineChart';

    protected static ?string $heading = 'Sales Pipeline';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberRealtime('crm.lead_pipeline', function () {
            return [
                'leads' => Lead::where('status', LeadStatus::Lead)->count(),
                'approach' => Lead::where('status', LeadStatus::Approach)->count(),
                'proposal' => Lead::where('status', LeadStatus::Proposal)->count(),
                'negotiation' => Lead::where('status', LeadStatus::Negotiation)->count(),
                'won' => Lead::where('status', LeadStatus::Won)->count(),

                'leads_value' => Lead::where('status', LeadStatus::Lead)->sum('estimated_amount'),
                'approach_value' => Lead::where('status', LeadStatus::Approach)->sum('estimated_amount'),
                'proposal_value' => Lead::where('status', LeadStatus::Proposal)->sum('estimated_amount'),
                'negotiation_value' => Lead::where('status', LeadStatus::Negotiation)->sum('estimated_amount'),
                'won_value' => Lead::where('status', LeadStatus::Won)->sum('estimated_amount'),
            ];
        });

        $stages = ['Lead', 'Approach', 'Proposal', 'Negotiation', 'Won'];
        $counts = [
            $data['leads'],
            $data['approach'],
            $data['proposal'],
            $data['negotiation'],
            $data['won'],
        ];

        $values = [
            $data['leads_value'],
            $data['approach_value'],
            $data['proposal_value'],
            $data['negotiation_value'],
            $data['won_value'],
        ];

        // Calculate conversion rates
        $total = array_sum(array_slice($counts, 0, 4)); // Exclude Won from denominator
        $conversionRates = [];
        foreach ($counts as $index => $count) {
            if ($index === 0) {
                $conversionRates[] = 100;
            } else {
                $conversionRates[] = $counts[$index - 1] > 0
                    ? round(($count / $counts[$index - 1]) * 100, 1)
                    : 0;
            }
        }

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
