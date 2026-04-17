<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\SalesPlanMonthly;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Illuminate\Support\Carbon;

class SalesPlanMonthlyObserver
{
    /**
     * Handle the SalesPlanMonthly "saved" event.
     */
    public function saved(SalesPlanMonthly $salesPlanMonthly): void
    {
        $this->syncToFinance($salesPlanMonthly);
    }

    /**
     * Synchronize budget changes to Finance Profitability Analysis.
     */
    protected function syncToFinance(SalesPlanMonthly $salesPlanMonthly): void
    {
        $salesPlan = $salesPlanMonthly->salesPlan;
        if (! $salesPlan) {
            return;
        }

        $lead = $salesPlan->lead;
        if (! $lead) {
            return;
        }

        // Find all Profitability Analyses that are still in Draft or Submitted status
        // Approved/Rejected/Converted PAs should not be automatically updated by CRM budget changes
        $analyses = $lead->profitabilityAnalyses()
            ->whereIn('status', [
                ProfitabilityAnalysisStatus::Draft,
                ProfitabilityAnalysisStatus::Submitted,
            ])
            ->get();

        if ($analyses->isEmpty()) {
            return;
        }

        // Convert month integer (1-12) to month name (e.g., "January") to match Finance schema
        $monthName = Carbon::create()->month($salesPlanMonthly->month)->format('F');

        foreach ($analyses as $analysis) {
            $financeMonthly = ProfitabilityAnalysisMonthly::where('profitability_analysis_id', $analysis->id)
                ->where('year', $salesPlanMonthly->year)
                ->where('month', $monthName)
                ->first();

            if ($financeMonthly) {
                // Update target_revenue (Budget RKAP) in Finance
                $financeMonthly->updateQuietly([
                    'target_revenue' => $salesPlanMonthly->budget_amount,
                ]);
            }
        }
    }
}
