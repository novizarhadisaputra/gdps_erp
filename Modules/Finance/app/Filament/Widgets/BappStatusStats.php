<?php

namespace Modules\Finance\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\AccrueRevenue;

class BappStatusStats extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return false;
    }
    protected function getStats(): array
    {
        $totalRevenue = (float) AccrueRevenue::sum('total_amount_actual');
        $pendingBappCount = (int) AccrueRevenue::whereNull('total_amount_actual')->count();
        $completedBappCount = (int) AccrueRevenue::whereNotNull('total_amount_actual')->count();

        return [
            Stat::make('Total Revenue (Actual)', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Accumulated actual revenue from all projects')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pending Accruals', (string) $pendingBappCount)
                ->description('Accrue revenue items awaiting actual amount')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Completed Accruals', (string) $completedBappCount)
                ->description('Successfully processed revenue items')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
}
