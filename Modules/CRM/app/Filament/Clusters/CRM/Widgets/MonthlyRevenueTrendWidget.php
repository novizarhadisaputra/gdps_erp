<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Locked;

class MonthlyRevenueTrendWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyRevenueTrendChart';

    #[Locked]
    public ?array $options = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    protected static ?string $heading = 'Monthly Revenue Performance (RoFo vs Actual)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 2;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getMonthlyRevenueTrend();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 350,
                'stacked' => false,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '60%',
                    'borderRadius' => 2,
                    'colors' => [
                        'ranges' => [
                            [
                                'from' => -1000000,
                                'to' => 0,
                                'color' => '#f97316', // Orange for cost
                            ],
                            [
                                'from' => 0,
                                'to' => 1000000,
                                'color' => '#fbbf24', // Yellow for revenue
                            ],
                        ],
                    ],
                ],
            ],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent'],
            ],
            'series' => [
                [
                    'name' => 'Revenue',
                    'data' => $data['revenueData'],
                ],
                [
                    'name' => 'Cost',
                    'data' => $data['costData'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['categories'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Value (Million IDR)',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],

            'tooltip' => [
                'y' => [
                    'formatter' => null,
                ],
            ],
            'colors' => ['#fbbf24', '#f97316'],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'center',
            ],
            'grid' => [
                'borderColor' => '#f1f1f1',
            ],
        ];
    }

    protected function getFooter(): string|Htmlable|View|null
    {
        return new HtmlString('<p class="text-xs text-gray-500 mt-2">Comparison of monthly Rolling Forecast (RoFo) against Actual Revenue realization.</p>');
    }
}
