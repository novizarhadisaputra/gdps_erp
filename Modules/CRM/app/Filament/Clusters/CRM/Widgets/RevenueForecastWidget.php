<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\View;

class RevenueForecastWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'revenueForecastChart';

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
        $months = [];
        $actualRevenue = [];
        $forecastedRevenue = [];
        $optimisticForecast = [];

        // Get historical data (last 6 months)
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $months[] = $date->format('M Y');

            // Actual revenue from won deals
            $actual = Lead::where('status', LeadStatus::Won)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('estimated_amount');

            $actualRevenue[] = round($actual / 1000000, 2);
            $forecastedRevenue[] = null;
            $optimisticForecast[] = null;
        }

        // Get forecast data (next 6 months)
        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->addMonths($i);
            $months[] = $date->format('M Y');

            // Get all active prospects for this period
            $leads = Lead::whereNotIn('status', [LeadStatus::Won, LeadStatus::ClosedLost])
                ->whereDate('expected_closing_date', '>=', $date->copy()->startOfMonth())
                ->whereDate('expected_closing_date', '<=', $date->copy()->endOfMonth())
                ->get();

            $weighted = $leads->sum(function ($lead) {
                return ($lead->estimated_amount * ($lead->probability ?? 50)) / 100;
            });

            // Optimistic forecast
            $optimistic = $leads->sum(function ($lead) {
                return $lead->estimated_amount * 0.9;
            });

            $actualRevenue[] = null;
            $forecastedRevenue[] = round($weighted / 1000000, 2);
            $optimisticForecast[] = round($optimistic / 1000000, 2);
        }

        $data = compact('months', 'actualRevenue', 'forecastedRevenue', 'optimisticForecast');

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
