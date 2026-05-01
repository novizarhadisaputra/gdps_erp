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

    /**
     * Widget Title
     */
    protected static ?string $heading = 'Revenue vs Expense Trend (Accruals)';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $data = AccrueRevenue::query()
            ->select('month', 'year',
                DB::raw('SUM(total_amount_actual) as total_revenue'),
                DB::raw('SUM(total_amount_expense_actual) as total_expense')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->limit(12)
            ->get();

        $labels = $data->map(fn ($item) => sprintf('%04d-%02d', $item->year, $item->month))->toArray();
        $revenue = $data->pluck('total_revenue')->toArray();
        $expense = $data->pluck('total_expense')->toArray();

        return [
            'chart' => [
                'type' => 'area',
                'height' => 350,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Actual Revenue',
                    'data' => $revenue,
                ],
                [
                    'name' => 'Actual Expense',
                    'data' => $expense,
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
                    'formatter' => 'function (value) { 
                        if (value >= 1000000000) return "Rp " + (value / 1000000000).toFixed(1) + "B";
                        if (value >= 1000000) return "Rp " + (value / 1000000).toFixed(1) + "M";
                        return "Rp " + value.toLocaleString("id-ID"); 
                    }',
                ],
            ],
            'colors' => ['#10b981', '#ef4444'], // Green for revenue, Red for expense
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
