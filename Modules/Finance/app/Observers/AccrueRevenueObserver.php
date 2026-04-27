<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class AccrueRevenueObserver
{
    /**
     * Handle the AccrueRevenue "saved" event.
     */
    public function saved(AccrueRevenue $accrueRevenue): void
    {
        $this->syncToMonthlyPerformance($accrueRevenue);
    }

    /**
     * Handle the AccrueRevenue "deleted" event.
     */
    public function deleted(AccrueRevenue $accrueRevenue): void
    {
        $this->syncToMonthlyPerformance($accrueRevenue, true);
    }

    /**
     * Handle the AccrueRevenue "restored" event.
     */
    public function restored(AccrueRevenue $accrueRevenue): void
    {
        $this->syncToMonthlyPerformance($accrueRevenue);
    }

    /**
     * Synchronize data to ProfitabilityAnalysisMonthly.
     */
    protected function syncToMonthlyPerformance(AccrueRevenue $accrueRevenue, bool $isDeleted = false): void
    {
        $project = $accrueRevenue->project;

        if (! $project?->profitability_analysis_id) {
            return;
        }

        $monthName = date('F', mktime(0, 0, 0, (int) $accrueRevenue->month, 1));

        $monthly = ProfitabilityAnalysisMonthly::where('profitability_analysis_id', $project->profitability_analysis_id)
            ->where('month', $monthName)
            ->where('year', $accrueRevenue->year)
            ->first();

        if ($monthly) {
            $monthly->update([
                'actual_revenue' => $isDeleted ? 0 : $accrueRevenue->amount_cost,
            ]);
        }
    }
}
