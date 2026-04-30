<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Livewire\Attributes\Locked;

class SalesPerformanceChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'salesPerformanceChart';

    #[Locked]
    public ?array $options = null;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return true;
    }

    protected static ?string $heading = 'Revenue Performance (Cumulative)';

    protected static ?int $contentHeight = 350;

    protected static ?int $sort = 5;

    protected function getOptions(): array
    {
        $data = app(CRMAnalyticsService::class)->getSalesPerformance();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 350,
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'RKAP Sales Plan (Target)',
                    'data' => $data['budgetData'],
                ],
                [
                    'name' => 'Rolling Forecast (RoFo)',
                    'data' => $data['forecastData'],
                ],
                [
                    'name' => 'Accrue Sales (Actual)',
                    'data' => $data['actualData'],
                ],
            ],
            'colors' => ['#4f46e5', '#ef4444', '#10b981'],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2,
            ],
            'markers' => [
                'size' => 4,
            ],
            'xaxis' => [
                'categories' => $data['months'],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Million IDR',
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'tooltip' => [
                'enabled' => true,
            ],
            'legend' => [
                'position' => 'top',
                'horizontalAlign' => 'left',
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05,
                    'stops' => [50, 100],
                ],
            ],
        ];
    }

    protected function getFooter(): string|Htmlable|View|null
    {
        return new HtmlString('<p class="text-xs text-gray-500 mt-2">Year-to-date (YTD) tracking of revenue performance compared to annual Sales Plan (RKAP).</p>');
    }
}
