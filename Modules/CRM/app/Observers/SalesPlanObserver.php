<?php

namespace Modules\CRM\Observers;

use Carbon\Carbon;
use Modules\CRM\Models\SalesPlan;
use Modules\CRM\Models\SalesPlanMonthly;

class SalesPlanObserver
{
    /**
     * Handle the SalesPlan "saving" event.
     */
    public function saving(SalesPlan $salesPlan): void
    {
        if (empty($salesPlan->revenue_distribution_planning) && $salesPlan->start_date && $salesPlan->end_date && $salesPlan->estimated_value > 0) {
            $this->generateDefaultDistribution($salesPlan);
        }
    }

    /**
     * Handle the SalesPlan "saved" event.
     */
    public function saved(SalesPlan $salesPlan): void
    {
        $this->syncMonthlyBreakdowns($salesPlan);
    }

    protected function generateDefaultDistribution(SalesPlan $salesPlan): void
    {
        $start = Carbon::parse($salesPlan->start_date)->startOfMonth();
        $end = Carbon::parse($salesPlan->end_date)->startOfMonth();

        $months = [];
        $current = $start->copy();

        $count = 0;
        while ($current <= $end) {
            $count++;
            $current->addMonth();
        }

        if ($count === 0) {
            return;
        }

        $average = $salesPlan->estimated_value / $count;

        $current = $start->copy();
        for ($i = 0; $i < $count; $i++) {
            $months[] = [
                'month' => $current->format('F Y'),
                'budget_amount' => round($average, 2),
                'forecast_amount' => round($average, 2),
                'actual_amount' => 0,
            ];
            $current->addMonth();
        }

        $salesPlan->revenue_distribution_planning = $months;
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

            $date = Carbon::parse($item['month']);

            SalesPlanMonthly::create([
                'sales_plan_id' => $salesPlan->id,
                'year' => $date->year,
                'month' => $date->month,
                'budget_amount' => $item['budget_amount'] ?? 0,
                'forecast_amount' => $item['forecast_amount'] ?? 0,
                'actual_amount' => $item['actual_amount'] ?? 0,
                'proposal_number' => $salesPlan->proposal_number,
                'project_code' => $salesPlan->project_code,
            ]);
        }
    }
}
