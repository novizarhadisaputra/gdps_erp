<?php

namespace Modules\CRM\Filament\Clusters\CRM\Widgets;

use App\Services\CRMAnalyticsService;
use Carbon\Carbon;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class PerformanceOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected CRMAnalyticsService $service;

    public function __construct()
    {
        $this->service = app(CRMAnalyticsService::class);
    }

    public static function canView(): bool
    {
        return true;
    }

    protected function getStats(): array
    {
        $metrics = $this->service->getFinancialPerformance();

        return [
            Stat::make('Total Target RoFo (YTD)', 'Rp '.number_format($metrics['target'], 0, ',', '.'))
                ->description('Aggregated target from Sales Plans')
                ->descriptionIcon(Heroicon::Flag)
                ->color('primary'),

            Stat::make('Actual Revenue (YTD)', 'Rp '.number_format($metrics['actual'], 0, ',', '.'))
                ->description('Realized revenue collected')
                ->descriptionIcon(Heroicon::CurrencyDollar)
                ->color($metrics['realization_rate'] >= 90 ? 'success' : ($metrics['realization_rate'] >= 70 ? 'warning' : 'danger')),

            Stat::make('Realization Rate', $metrics['realization_rate'].'%')
                ->description('Target accomplishment rate')
                ->descriptionIcon($metrics['realization_rate'] >= 100 ? Heroicon::CheckBadge : Heroicon::ArrowPath)
                ->color($metrics['realization_rate'] >= 90 ? 'success' : ($metrics['realization_rate'] >= 70 ? 'warning' : 'danger'))
                ->chart($this->getRealizationTrendData()),

            Stat::make('Realized Gross Profit', 'Rp '.number_format($metrics['gross_profit'], 0, ',', '.'))
                ->description('Revenue - Direct Costs')
                ->descriptionIcon(Heroicon::Banknotes)
                ->color('success'),

            Stat::make('Total EBIT (YTD)', 'Rp '.number_format($metrics['ebit'], 0, ',', '.'))
                ->description('Earnings Before Interest & Taxes')
                ->descriptionIcon(Heroicon::ChartBar)
                ->color('info'),
        ];
    }

    protected function getRealizationTrendData(): array
    {
        $currentYear = Carbon::now()->year;
        $data = [];

        // Simple monthly realization trend for the sparkline
        for ($m = 1; $m <= 12; $m++) {
            $monthName = Carbon::create($currentYear, $m, 1)->format('F');
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
