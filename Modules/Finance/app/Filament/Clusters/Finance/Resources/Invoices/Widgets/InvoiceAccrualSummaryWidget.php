<?php

namespace Modules\Finance\Filament\Clusters\Finance\Resources\Invoices\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
use Modules\Finance\Models\Invoice;

class InvoiceAccrualSummaryWidget extends BaseWidget
{
    public ?Invoice $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $totalLinked = $this->record->accrueInvoiceMappings()->count();
        $totalAllocated = $this->record->accrueInvoiceMappings()->sum('allocated_amount');
        $reversedCount = $this->record->accrueInvoiceMappings()->where('status', AccrueInvoiceMappingStatus::Reversed)->count();

        return [
            Stat::make('Linked Accruals', $totalLinked)
                ->description('Number of accrual items linked to this invoice')
                ->icon('heroicon-m-link'),
            Stat::make('Total Allocation', 'IDR '.number_format($totalAllocated, 2))
                ->description('Total amount to be reversed from accruals')
                ->icon('heroicon-m-currency-dollar')
                ->color('primary'),
            Stat::make('Reversal Status', $reversedCount.' / '.$totalLinked)
                ->description($reversedCount === $totalLinked ? 'Fully Reversed' : 'Pending Reversal')
                ->icon('heroicon-m-arrow-path')
                ->color($reversedCount === $totalLinked ? 'success' : 'warning'),
        ];
    }
}
