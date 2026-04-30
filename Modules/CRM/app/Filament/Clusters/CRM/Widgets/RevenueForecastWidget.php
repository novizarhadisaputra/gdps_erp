<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Locked;

class RevenueForecastWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueForecastChart';

    #[Locked]
    public ?array $options = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    protected static ?string $heading = 'Revenue Forecast';

    protected static ?int $contentHeight = 320;

    protected static ?int $sort = 4;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getGranularRevenueForecast();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 320,
                'toolbar' => [
                    'show' => true,
                ],
                'zoom' => [
                    'enabled' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Actual Revenue',
                    'data' => $data['actualRevenue'],
                ],
                [
                    'name' => 'Forecasted Revenue',
                    'data' => $data['forecastedRevenue'],
                ],
                [
                    'name' => 'Optimistic Forecast',
                    'data' => $data['optimisticForecast'],
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'opacityFrom' => 0.6,
                    'opacityTo' => 0.1,
                ],
            ],
            'colors' => ['#10b981', '#6366f1', '#f59e0b'],
            'xaxis' => [
                'categories' => $data['months'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Revenue (Million IDR)',
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
            'tooltip' => [
                'enabled' => true,
                'theme' => 'dark',
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
                'fontFamily' => 'inherit',
            ],

        ];
    }

    protected function getFooter(): string|Htmlable|View|null
    {
        return new HtmlString('<p class="text-xs text-gray-500 mt-2">Future revenue projection based on active leads pipeline and expected closing dates.</p>');
    }
}
