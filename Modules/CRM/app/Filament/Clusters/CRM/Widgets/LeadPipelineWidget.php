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

        $data = $cache->rememberRealtime('crm.lead_pipeline_levels', function () {
            $levels = [
                'level_1' => [LeadStatus::Lead, LeadStatus::Approach],
                'level_2' => [LeadStatus::Proposal],
                'level_3' => [LeadStatus::Negotiation],
                'level_4' => [LeadStatus::Contract],
            ];

            $results = [];

            foreach ($levels as $key => $statuses) {
                $leads = Lead::whereIn('status', $statuses)
                    ->with('profitabilityAnalyses')
                    ->get();

                $results[$key . '_count'] = $leads->count();
                $results[$key . '_value'] = $leads->sum(function ($lead) {
                    $pa = $lead->profitabilityAnalyses->sortByDesc('created_at')->first();
                    
                    return $pa 
                        ? (float) $pa->revenue_per_month 
                        : (float) $lead->estimated_amount;
                });
            }

            return $results;
        });

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
