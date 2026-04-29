<?php

namespace Modules\Finance\Filament\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\Finance\Services\ManpowerCostingService;

class ManpowerCompositionDonut extends ApexChartWidget
{
    protected static ?string $chartId = 'manpowerCompositionDonut';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }

    protected static ?string $heading = 'Average Manpower Cost Composition';

    protected function getOptions(): array
    {
        $service = app(ManpowerCostingService::class);
        $items = ManpowerTemplateItem::query()->limit(10)->get();

        $totals = [
            'Basic Salary' => 0,
            'BPJS' => 0,
            'Tax (PPh 21)' => 0,
            'Accruals (THR/Komp)' => 0,
            'Admin/Mgmt Fee' => 0,
        ];

        foreach ($items as $item) {
            $calc = $service->calculate($item->toArray());
            $totals['Basic Salary'] += $calc['total_monthly_salary'] ?? 0;
            $totals['BPJS'] += $calc['bpjs_total'] ?? 0;
            $totals['Tax (PPh 21)'] += $calc['pph21']['total'] ?? 0;
            $totals['Accruals (THR/Komp)'] += ($calc['accruals']['thr'] ?? 0) + ($calc['accruals']['compensation'] ?? 0);
            $totals['Admin/Mgmt Fee'] += ($calc['admin_fee'] ?? 0) + ($calc['management_fee'] ?? 0);
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => array_values($totals),
            'labels' => array_keys($totals),
            'legend' => [
                'position' => 'bottom',
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'colors' => ['#10b981', '#3b82f6', '#f59e0b', '#ef4444', '#8b5cf6'],
        ];
    }
}
