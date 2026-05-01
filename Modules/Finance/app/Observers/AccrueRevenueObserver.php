<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Carbon;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class AccrueRevenueObserver
{
    /**
     * Handle the AccrueRevenue "creating" event.
     */
    public function creating(AccrueRevenue $accrueRevenue): void
    {
        if (filled($accrueRevenue->number) && $accrueRevenue->number !== 'Auto-generated') {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = AccrueRevenue::withTrashed()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $accrueRevenue->year = (int) $year;
        $accrueRevenue->sequence_number = $sequence;
        $accrueRevenue->number = sprintf('GDPS/UB/ACR-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the AccrueRevenue "saved" event.
     */
    public function saved(AccrueRevenue $accrueRevenue): void
    {
        // 1. Aggregate totals from items
        $totals = $accrueRevenue->items()
            ->selectRaw('
                SUM(amount_estimated) as total_estimated, 
                SUM(amount_actual) as total_actual,
                SUM(amount_expense_estimated) as total_expense_estimated,
                SUM(amount_expense_actual) as total_expense_actual
            ')
            ->first();

        $totalEstimated = $totals->total_estimated ?? 0;
        $totalActual = $totals->total_actual ?? 0;
        $totalExpenseEstimated = $totals->total_expense_estimated ?? 0;
        $totalExpenseActual = $totals->total_expense_actual ?? 0;

        // 2. Determine status based on items
        $totalItems = $accrueRevenue->items()->count();
        $reversedItems = $accrueRevenue->items()->where('is_reversed', true)->count();
        $hasInvoices = $accrueRevenue->items()->whereNotNull('invoice_id')->count();

        $status = AccrueRevenueStatus::Open;
        if ($totalItems > 0) {
            if ($reversedItems === $totalItems) {
                $status = AccrueRevenueStatus::Reversed;
            } elseif ($hasInvoices === $totalItems) {
                $status = AccrueRevenueStatus::Closed;
            }
        }

        // 3. Update header quietly to avoid infinite loop
        $accrueRevenue->updateQuietly([
            'total_amount_estimated' => $totalEstimated,
            'total_amount_actual' => $totalActual,
            'total_amount_expense_estimated' => $totalExpenseEstimated,
            'total_amount_expense_actual' => $totalExpenseActual,
            'status' => $status,
        ]);

        // 4. Sync to external performance tables
        $this->syncPerformance($accrueRevenue, $totalActual);
    }

    /**
     * Handle the AccrueRevenue "deleted" event.
     */
    public function deleted(AccrueRevenue $accrueRevenue): void
    {
        $this->syncPerformance($accrueRevenue, 0);
    }

    /**
     * Synchronize data to ProfitabilityAnalysisMonthly.
     * Downstream sync to SalesPlanMonthly is handled by ProfitabilityAnalysisMonthlyObserver.
     */
    protected function syncPerformance(AccrueRevenue $accrueRevenue, $totalActual): void
    {
        $project = $accrueRevenue->project;
        if (! $project || ! $project->profitability_analysis_id) {
            return;
        }

        $monthName = Carbon::create()->month((int) $accrueRevenue->month)->format('F');

        $monthlyPerformance = ProfitabilityAnalysisMonthly::where('profitability_analysis_id', $project->profitability_analysis_id)
            ->where('month', $monthName)
            ->where('year', $accrueRevenue->year)
            ->first();

        if ($monthlyPerformance) {
            $monthlyPerformance->update([
                'actual_revenue' => $totalActual,
            ]);
        }
    }
}
