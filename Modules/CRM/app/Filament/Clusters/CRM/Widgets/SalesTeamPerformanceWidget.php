<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Models\Lead;

class SalesTeamPerformanceWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'salesTeamPerformanceChart';

    protected static ?string $heading = 'Sales Team Performance';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 6;

    protected function getOptions(): array
    {
        $cache = app(AnalyticsCacheService::class);

        $data = $cache->rememberHourly('crm.team_performance', function () {
            $users = Lead::with('user:id,name')
                ->whereNotNull('user_id')
                ->get()
                ->groupBy('user_id');

            $names = [];
            $createdCounts = [];
            $convertedCounts = [];
            $revenues = [];

            foreach ($users as $userId => $leads) {
                $userName = $leads->first()->user->name ?? 'Unknown';
                $names[] = $userName;
                $createdCounts[] = $leads->count();
                $convertedCounts[] = $leads->where('status', \Modules\CRM\Enums\LeadStatus::Won)->count();
                $revenues[] = round($leads->where('status', \Modules\CRM\Enums\LeadStatus::Won)->sum('estimated_amount') / 1000000, 2);
            }

            return compact('names', 'createdCounts', 'convertedCounts', 'revenues');
        });

        if (empty($data['names'])) {
            $data = [
                'names' => ['No Data'],
                'createdCounts' => [0],
                'convertedCounts' => [0],
                'revenues' => [0],
            ];
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Leads Created',
                    'data' => $data['createdCounts'],
                ],
                [
                    'name' => 'Leads Won',
                    'data' => $data['convertedCounts'],
                ],
                [
                    'name' => 'Revenue (Million)',
                    'data' => $data['revenues'],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'endingShape' => 'rounded',
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
            'colors' => ['#6366f1', '#10b981', '#f59e0b'],
            'xaxis' => [
                'categories' => $data['names'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                    'rotate' => -45,
                    'rotateAlways' => false,
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Count / Revenue',
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
            'fill' => [
                'opacity' => 1,
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
