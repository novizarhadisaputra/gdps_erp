<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\SalesPlan;
use Modules\CRM\Models\SalesPlanMonthly;

class SalesPlanObserver
{
    /**
     * Handle the SalesPlan "saved" event.
     */
    public function saved(SalesPlan $salesPlan): void
    {
        $this->syncMonthlyBreakdowns($salesPlan);
    }

    /**
     * Handle the SalesPlan "deleted" event.
     */
    public function deleted(SalesPlan $salesPlan): void
    {
        $salesPlan->monthlyBreakdowns()->delete();
    }

    /**
     * Sync the JSON distribution to the flat table.
     */
    protected function syncMonthlyBreakdowns(SalesPlan $salesPlan): void
    {
        // Clear existing breakdowns
        $salesPlan->monthlyBreakdowns()->delete();

        $distribution = $salesPlan->revenue_distribution_planning;

        if (! is_array($distribution)) {
            return;
        }

        foreach ($distribution as $item) {
            // item['month'] example: "February 2026"
            // We need to parse it or use the value if we added it in the form

            $date = \Carbon\Carbon::parse($item['month']);

            SalesPlanMonthly::create([
                'sales_plan_id' => $salesPlan->id,
                'year' => $date->year,
                'month' => $date->month,
                'amount' => $item['amount'],
                'proposal_number' => $salesPlan->proposal_number,
                'project_code' => $salesPlan->project_code,
            ]);
        }
    }
}
