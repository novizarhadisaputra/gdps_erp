<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\AccrueRevenues\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Models\AccrueRevenue;

class AccrualInvoicingProgressWidget extends BaseWidget
{
    public ?AccrueRevenue $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $accrued = $this->record->total_amount_estimated;
        $invoiced = $this->record->total_amount_actual;
        $remaining = $accrued - $invoiced;
        $percentage = $accrued > 0 ? ($invoiced / $accrued) * 100 : 0;

        return [
            Stat::make('Total Accrued', 'IDR '.number_format($accrued, 2))
                ->icon('heroicon-m-presentation-chart-line'),
            Stat::make('Invoiced Progress', number_format($percentage, 1).'%')
                ->description('IDR '.number_format($invoiced, 2).' Invoiced')
                ->icon('heroicon-m-check-circle')
                ->color($percentage >= 100 ? 'success' : 'info')
                ->chart([$percentage, 100 - $percentage]),
            Stat::make('Remaining Balance', 'IDR '.number_format($remaining, 2))
                ->description($remaining > 0 ? 'Pending invoicing' : 'Fully invoiced')
                ->icon('heroicon-m-clock')
                ->color($remaining > 0 ? 'warning' : 'success'),
        ];
    }
}
