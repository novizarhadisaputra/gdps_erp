<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\CRM\Models\SalesPlanMonthly;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Illuminate\Support\Carbon;

class ProfitabilityAnalysisMonthlyObserver
{
    public function creating(ProfitabilityAnalysisMonthly $monthly): void
    {
        $monthly->loadMissing('profitabilityAnalysis.lead.salesPlan.monthlyBreakdowns');
        $lead = $monthly->profitabilityAnalysis?->lead;
        
        if ($lead && $lead->salesPlan) {
            // Convert month name (e.g. "January") to integer (1-12)
            try {
                $monthInt = Carbon::parse($monthly->month)->month;
            } catch (\Exception $e) {
                $monthInt = 0;
            }

            $budget = $lead->salesPlan->monthlyBreakdowns()
                ->where('year', $monthly->year)
                ->where('month', $monthInt)
                ->first();
                
            if ($budget) {
                $monthly->target_revenue = $budget->budget_amount;
            }
        }

        if (! $monthly->target_revenue && $monthly->profitabilityAnalysis) {
            $monthly->target_revenue = $monthly->profitabilityAnalysis->revenue_per_month;
        }

        if (! $monthly->status) {
            $monthly->status = ProfitabilityAnalysisMonthlyStatus::Draft;
        }
    }

    public function saved(ProfitabilityAnalysisMonthly $monthly): void
    {
        $this->syncToSalesPlan($monthly);
    }

    public function deleted(ProfitabilityAnalysisMonthly $monthly): void
    {
        $this->syncToSalesPlan($monthly, true);
    }

    /**
     * Synchronize actuals and forecasts to CRM Sales Plan Monthly records.
     */
    protected function syncToSalesPlan(ProfitabilityAnalysisMonthly $monthly, bool $isDeleted = false): void
    {
        $monthly->loadMissing('profitabilityAnalysis.lead.salesPlan');

        $lead = $monthly->profitabilityAnalysis?->lead;
        if (! $lead || ! $lead->salesPlan) {
            return;
        }

        $salesPlan = $lead->salesPlan;

        // Convert month name (e.g. "January") to integer (1-12) for CRM
        try {
            $monthInt = Carbon::parse($monthly->month)->month;
        } catch (\Exception $e) {
            $monthInt = 0;
        }

        // Find or create the corresponding Sales Plan Monthly record
        $crmMonthly = SalesPlanMonthly::firstOrNew([
            'sales_plan_id' => $salesPlan->id,
            'year' => $monthly->year,
            'month' => $monthInt,
        ]);

        if ($isDeleted) {
            $crmMonthly->actual_amount = 0;
            $crmMonthly->forecast_amount = 0;
        } else {
            $crmMonthly->actual_amount = $monthly->actual_revenue ?? 0;
            $crmMonthly->forecast_amount = $monthly->forecast_revenue ?? 0;
        }

        $crmMonthly->save();
    }
}
