<?php

namespace Modules\Finance\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\AccrueRevenue;

class AccrualStatusStats extends BaseWidget
{
    protected function getStats(): array
    {
        $openCount = AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Open)->count();
        $openAmount = AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Open)->sum('total_amount_estimated');

        $closedCount = AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Closed)->count();
        $closedAmount = AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Closed)->sum('total_amount_actual');
        $closedExpense = AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Closed)->sum('total_amount_expense_actual');

        return [
            Stat::make('Open Accruals', $openCount)
                ->description('Est: IDR '.number_format($openAmount, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Closed Accruals', $closedCount)
                ->description('Rev: IDR '.number_format($closedAmount, 0, ',', '.').' | Exp: IDR '.number_format($closedExpense, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Pending Reversals', AccrueRevenue::where('status', \Modules\Finance\Enums\AccrueRevenueStatus::Open)->whereNotNull('sap_reference')->count())
                ->description('Ready for invoice matching')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
        ];
    }
}
