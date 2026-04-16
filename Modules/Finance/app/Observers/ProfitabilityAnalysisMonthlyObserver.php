<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Modules\CRM\Models\SalesPlanMonthly;

class ProfitabilityAnalysisMonthlyObserver
{
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

        // Find or create the corresponding Sales Plan Monthly record
        $crmMonthly = SalesPlanMonthly::firstOrNew([
            'sales_plan_id' => $salesPlan->id,
            'year' => $monthly->year,
            'month' => $monthly->month,
        ]);

        if ($isDeleted) {
            $crmMonthly->actual_amount = 0;
            $crmMonthly->forecast_amount = 0;
        } else {
            $crmMonthly->actual_amount = $monthly->actual_revenue;
            $crmMonthly->forecast_amount = $monthly->forecast_revenue;
        }

        $crmMonthly->save();
    }
}
