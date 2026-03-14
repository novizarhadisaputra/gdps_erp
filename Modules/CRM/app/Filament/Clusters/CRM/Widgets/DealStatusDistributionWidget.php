<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

class DealStatusDistributionWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'dealStatusDistributionChart';

    protected static ?string $heading = 'Deal Status Distribution';

    protected static ?int $contentHeight = 320;

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberRealtime('crm.deal_status_distribution', function () {
            return [
                'lead' => Lead::where('status', LeadStatus::Lead)->count(),
                'approach' => Lead::where('status', LeadStatus::Approach)->count(),
                'proposal' => Lead::where('status', LeadStatus::Proposal)->count(),
                'negotiation' => Lead::where('status', LeadStatus::Negotiation)->count(),
                'won' => Lead::where('status', LeadStatus::Won)->count(),
                'closed_lost' => Lead::where('status', LeadStatus::ClosedLost)->count(),

                'lead_value' => Lead::where('status', LeadStatus::Lead)->sum('estimated_amount'),
                'approach_value' => Lead::where('status', LeadStatus::Approach)->sum('estimated_amount'),
                'proposal_value' => Lead::where('status', LeadStatus::Proposal)->sum('estimated_amount'),
                'negotiation_value' => Lead::where('status', LeadStatus::Negotiation)->sum('estimated_amount'),
                'won_value' => Lead::where('status', LeadStatus::Won)->sum('estimated_amount'),
                'closed_lost_value' => Lead::where('status', LeadStatus::ClosedLost)->sum('estimated_amount'),
            ];
        });

        $labels = ['Lead', 'Approach', 'Proposal', 'Negotiation', 'Won', 'Closed Lost'];
        $counts = [
            $data['lead'],
            $data['approach'],
            $data['proposal'],
            $data['negotiation'],
            $data['won'],
            $data['closed_lost'],
        ];

        $values = [
            $data['lead_value'],
            $data['approach_value'],
            $data['proposal_value'],
            $data['negotiation_value'],
            $data['won_value'],
            $data['closed_lost_value'],
        ];

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $counts,
            'labels' => $labels,
            'colors' => ['#94a3b8', '#3b82f6', '#6366f1', '#f59e0b', '#10b981', '#ef4444'],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
                'labels' => [
                    'colors' => '#6b7280',
                ],
            ],
            'plotOptions' => [
                'pie' => [
                    'donut' => [
                        'size' => '65%',
                        'labels' => [
                            'show' => true,
                            'name' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                            ],
                            'value' => [
                                'show' => true,
                                'fontFamily' => 'inherit',
                                'formatter' => null,
                            ],
                            'total' => [
                                'show' => true,
                                'label' => 'Total Deals',
                                'fontFamily' => 'inherit',
                                'formatter' => null,
                            ],
                        ],
                    ],
                ],
            ],
            'dataLabels' => [
                'enabled' => true,
                'formatter' => null,
            ],
            'tooltip' => [
                'theme' => 'dark',
                'y' => [
                    'formatter' => null,
                ],
            ],
        ];
    }
}
