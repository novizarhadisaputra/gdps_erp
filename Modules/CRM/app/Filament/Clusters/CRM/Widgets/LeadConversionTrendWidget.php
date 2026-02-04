<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

class LeadConversionTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'leadConversionTrendChart';

    protected static ?string $heading = 'Lead Conversion Trend';

    protected static ?int $contentHeight = 300;

    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('crm.conversion_trend', function () {
            $months = [];
            $wonData = [];
            $lostData = [];
            $conversionRateData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();

                $totalLeads = Lead::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $wonLeads = Lead::where('status', LeadStatus::Won)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();
                $lostLeads = Lead::where('status', LeadStatus::ClosedLost)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->count();

                $conversionRate = $totalLeads > 0 ? round(($wonLeads / $totalLeads) * 100, 1) : 0;

                $months[] = $date->format('M Y');
                $wonData[] = $wonLeads;
                $lostData[] = $lostLeads;
                $conversionRateData[] = $conversionRate;
            }

            return compact('months', 'wonData', 'lostData', 'conversionRateData');
        });

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
