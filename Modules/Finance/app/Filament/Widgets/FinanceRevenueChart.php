<?php

namespace Modules\Finance\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\Finance\Models\AccrueRevenue;

class FinanceRevenueChart extends ApexChartWidget
{
    /**
     * Chart Id
     */
    protected static ?string $chartId = 'financeRevenueChart';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Monthly Accrue Revenue Trend';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $data = AccrueRevenue::query()
            ->select('month', 'year', DB::raw('SUM(total_amount_actual) as total'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->limit(6)
            ->get();

        $labels = $data->map(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month))->toArray();
        $values = $data->pluck('total')->toArray();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Actual Revenue',
                    'data' => $values,
                ],
            ],
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => [
                        'fontWeight' => 600,
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'formatter' => 'function (value) { return "Rp " + value.toLocaleString("id-ID"); }',
                ],
            ],
            'colors' => ['#f59e0b'],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 1,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.9,
                    'stops' => [0, 90, 100],
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'stroke' => [
                'curve' => 'smooth',
            ],
        ];
    }
}
