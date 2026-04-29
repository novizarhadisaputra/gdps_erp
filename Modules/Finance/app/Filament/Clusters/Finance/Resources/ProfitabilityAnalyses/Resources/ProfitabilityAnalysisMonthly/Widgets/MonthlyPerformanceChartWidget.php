<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Resources\ProfitabilityAnalysisMonthly\Widgets;

use Illuminate\Database\Eloquent\Model;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyPerformanceChartWidget extends ApexChartWidget
{
    protected static ?string $chartId = 'monthlyPerformanceChart';

    protected static ?string $heading = 'Project Revenue Performance (Target vs Actual)';

    protected static ?int $contentHeight = 300;

    public ?Model $record = null;

    protected function getOptions(): array
    {
        if (! $this->record) {
            return [];
        }

        $project = $this->record;

        if ($this->record instanceof \Modules\Finance\Models\ProfitabilityAnalysisMonthly) {
            $project = $this->record->profitabilityAnalysis;
        }

        if (! $project) {
            return [];
        }

        $monthlies = $project->monthlies()
            ->orderBy('year', 'asc')
            ->orderByRaw("CASE month 
                WHEN 'January' THEN 1 
                WHEN 'February' THEN 2 
                WHEN 'March' THEN 3 
                WHEN 'April' THEN 4 
                WHEN 'May' THEN 5 
                WHEN 'June' THEN 6 
                WHEN 'July' THEN 7 
                WHEN 'August' THEN 8 
                WHEN 'September' THEN 9 
                WHEN 'October' THEN 10 
                WHEN 'November' THEN 11 
                WHEN 'December' THEN 12 
                ELSE 13 END ASC")
            ->get();

        $categories = [];
        $targetData = [];
        $forecastData = [];
        $actualData = [];

        foreach ($monthlies as $monthly) {
            $categories[] = substr($monthly->month, 0, 3).' '.$monthly->year;
            $targetData[] = (float) $monthly->target_revenue;
            $forecastData[] = (float) $monthly->forecast_revenue;
            $actualData[] = (float) $monthly->actual_revenue;
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => [3, 3, 4],
            ],
            'series' => [
                [
                    'name' => 'Target Revenue',
                    'type' => 'line',
                    'data' => $targetData,
                ],
                [
                    'name' => 'Forecast Revenue (RoFo)',
                    'type' => 'line',
                    'data' => $forecastData,
                ],
                [
                    'name' => 'Actual Revenue',
                    'type' => 'area',
                    'data' => $actualData,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
            ],
            'yaxis' => [
                'labels' => [
                    'formatter' => 'function (val) { return "IDR " + val.toLocaleString() }',
                ],
            ],
            'colors' => ['#6366f1', '#f59e0b', '#10b981'], // Indigo, Amber, Emerald
            'legend' => [
                'position' => 'top',
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
}
