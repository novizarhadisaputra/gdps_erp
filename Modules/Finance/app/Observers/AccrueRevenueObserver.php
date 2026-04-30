<?php

namespace Modules\Finance\Observers;

use Illuminate\Support\Carbon;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class AccrueRevenueObserver
{
    /**
     * Handle the AccrueRevenue "saved" event.
     */
    public function saved(AccrueRevenue $accrueRevenue): void
    {
        // 1. Aggregate totals from items
        $totals = $accrueRevenue->items()
            ->selectRaw('SUM(amount_estimated) as total_estimated, SUM(amount_actual) as total_actual')
            ->first();

        $totalEstimated = $totals->total_estimated ?? 0;
        $totalActual = $totals->total_actual ?? 0;

        // 2. Update header quietly to avoid infinite loop
        $accrueRevenue->updateQuietly([
            'total_amount_estimated' => $totalEstimated,
            'total_amount_actual' => $totalActual,
        ]);

        // 3. Sync to external performance tables
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
