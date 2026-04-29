<?php

namespace Modules\Finance\Observers;

use Modules\CRM\Models\SalesPlanMonthly;
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
     * Synchronize data to ProfitabilityAnalysisMonthly and SalesPlanMonthly.
     */
    protected function syncPerformance(AccrueRevenue $accrueRevenue, $totalActual): void
    {
        $project = $accrueRevenue->project;
        if (! $project) {
            return;
        }

        // 1. Sync to ProfitabilityAnalysisMonthly
        if ($project->profitability_analysis_id) {
            $monthName = date('F', mktime(0, 0, 0, (int) $accrueRevenue->month, 1));

            ProfitabilityAnalysisMonthly::where('profitability_analysis_id', $project->profitability_analysis_id)
                ->where('month', $monthName)
                ->where('year', $accrueRevenue->year)
                ->update(['actual_revenue' => $totalActual]);
        }

        // 2. Sync to SalesPlanMonthly
        $salesPlanId = $project->lead?->salesPlan?->id;
        if ($salesPlanId) {
            SalesPlanMonthly::where('sales_plan_id', $salesPlanId)
                ->where('month', $accrueRevenue->month)
                ->where('year', $accrueRevenue->year)
                ->update(['actual_amount' => $totalActual]);
        }
    }
}
