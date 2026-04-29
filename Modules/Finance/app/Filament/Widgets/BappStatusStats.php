<?php

namespace Modules\Finance\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\AccrueRevenue;

class BappStatusStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = AccrueRevenue::sum('total_amount_actual');
        $pendingBapp = AccrueRevenue::whereNull('total_amount_actual')->count(); // Example logic
        $completedBapp = AccrueRevenue::whereNotNull('total_amount_actual')->count();

        return [
            Stat::make('Total Revenue (Actual)', 'Rp '.number_format($totalRevenue, 0, ',', '.'))
                ->description('Accumulated actual revenue from all projects')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pending Accruals', $pendingBapp)
                ->description('Accrue revenue items awaiting actual amount')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Completed Accruals', $completedBapp)
                ->description('Successfully processed revenue items')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
