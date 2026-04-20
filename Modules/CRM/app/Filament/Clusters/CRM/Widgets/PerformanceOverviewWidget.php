<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\AnalyticsCacheService;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class PerformanceOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $cache = app(AnalyticsCacheService::class);

        return $cache->rememberHourly('crm.so_performance_overview', function () {
            $currentYear = Carbon::now()->year;

            // 1. Total Target RoFo (YTD)
            $totalTarget = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->sum('target_revenue');

            // 2. Total Actual Revenue (YTD)
            $totalActual = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->sum('actual_revenue');

            // 3. Realization Rate
            $realizationRate = $totalTarget > 0 
                ? round(($totalActual / $totalTarget) * 100, 1) 
                : 0;

            // 4. Actual Gross Profit
            $totalGrossProfit = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->sum('gross_profit');

            // 5. EBIT
            $totalEbit = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->sum('ebit');

            return [
                Stat::make('Total Target RoFo (YTD)', 'Rp ' . number_format($totalTarget, 0, ',', '.'))
                    ->description('Aggregated target from Sales Plans')
                    ->descriptionIcon(Heroicon::Flag)
                    ->color('primary'),

                Stat::make('Actual Revenue (YTD)', 'Rp ' . number_format($totalActual, 0, ',', '.'))
                    ->description('Realized revenue collected')
                    ->descriptionIcon(Heroicon::CurrencyDollar)
                    ->color($realizationRate >= 90 ? 'success' : ($realizationRate >= 70 ? 'warning' : 'danger')),

                Stat::make('Realization Rate', $realizationRate . '%')
                    ->description('Target accomplishment rate')
                    ->descriptionIcon($realizationRate >= 100 ? Heroicon::CheckBadge : Heroicon::ArrowPath)
                    ->color($realizationRate >= 90 ? 'success' : ($realizationRate >= 70 ? 'warning' : 'danger'))
                    ->chart($this->getRealizationTrendData()),

                Stat::make('Realized Gross Profit', 'Rp ' . number_format($totalGrossProfit, 0, ',', '.'))
                    ->description('Revenue - Direct Costs')
                    ->descriptionIcon(Heroicon::Banknotes)
                    ->color('success'),

                Stat::make('Total EBIT (YTD)', 'Rp ' . number_format($totalEbit, 0, ',', '.'))
                    ->description('Earnings Before Interest & Taxes')
                    ->descriptionIcon(Heroicon::ChartBar)
                    ->color('info'),
            ];
        });
    }

    protected function getRealizationTrendData(): array
    {
        $currentYear = Carbon::now()->year;
        $data = [];

        // Simple monthly realization trend for the sparkline
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create()->month($m)->format('F');
            $target = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->where('month', $monthName)
                ->sum('target_revenue');
            $actual = ProfitabilityAnalysisMonthly::where('year', $currentYear)
                ->where('month', $monthName)
                ->sum('actual_revenue');

            $data[] = $target > 0 ? ($actual / $target) * 100 : 0;
        }

        return $data;
    }
}
