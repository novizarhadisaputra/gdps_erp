<?php

namespace Modules\CRM\Observers;

use Carbon\Carbon;
use Modules\CRM\Enums\ProrationMethod;
use Modules\CRM\Models\SalesPlan;
use Modules\CRM\Models\SalesPlanMonthly;

class SalesPlanObserver
{
    /**
     * Handle the SalesPlan "creating" event.
     */
    public function creating(SalesPlan $salesPlan): void
    {
        if (auth()->check()) {
            $salesPlan->ams_id = $salesPlan->ams_id ?? auth()->id();
        }
    }

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
        $startDate = $salesPlan->start_date ? Carbon::parse($salesPlan->start_date) : null;
        $endDate = $salesPlan->end_date ? Carbon::parse($salesPlan->end_date) : null;
        $totalValue = (float) $salesPlan->estimated_value;
        $cutoffDay = (int) ($salesPlan->cutoff_day ?? 25);
        $method = $salesPlan->proration_method;
        $topDays = (int) ($salesPlan->top_days ?? 0);

        if (! $startDate || ! $endDate || $totalValue <= 0 || ! $method) {
            return;
        }

        if ($method === ProrationMethod::Equal) {
            $start = $startDate->copy()->startOfMonth();
            $end = $endDate->copy()->startOfMonth();

            $count = 0;
            $temp = $start->copy();
            while ($temp <= $end) {
                $count++;
                $temp->addMonth();
            }

            if ($count === 0) {
                return;
            }

            $average = $totalValue / $count;
            $months = [];
            $current = $start->copy();
            $runningSum = 0;

            for ($i = 0; $i < $count; $i++) {
                if ($i === $count - 1) {
                    $amount = $totalValue - $runningSum;
                } else {
                    $amount = round($average, 2);
                    $runningSum += $amount;
                }

                $months[] = [
                    'month' => $current->format('F Y'),
                    'budget_amount' => $amount,
                    'forecast_amount' => $amount,
                    'actual_amount' => 0,
                ];
                $current->addMonth();
            }
            $salesPlan->revenue_distribution_planning = $months;

            return;
        }

        // Daily Prorated Logic with Cut-off Day
        $totalDays = $startDate->diffInDays($endDate) + 1;
        if ($totalDays <= 0) {
            return;
        }

        $distribution = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            if ($current->day <= $cutoffDay) {
                $cycleEnd = $current->copy()->day($cutoffDay);
            } else {
                $cycleEnd = $current->copy()->addMonthNoOverflow()->day($cutoffDay);
            }

            if ($cycleEnd > $endDate) {
                $cycleEnd = $endDate->copy();
            }

            $daysInCycle = $current->diffInDays($cycleEnd) + 1;
            $amount = ($daysInCycle / $totalDays) * $totalValue;

            $monthLabel = $cycleEnd->format('F Y');

            $found = false;
            foreach ($distribution as &$item) {
                if ($item['month'] === $monthLabel) {
                    $item['budget_amount'] += $amount;
                    $item['forecast_amount'] += $amount;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $distribution[] = [
                    'month' => $monthLabel,
                    'budget_amount' => $amount,
                    'forecast_amount' => $amount,
                    'actual_amount' => 0,
                ];
            }

            $current = $cycleEnd->copy()->addDay();
        }

        $runningSum = 0;
        $count = count($distribution);
        foreach ($distribution as $index => &$item) {
            if ($index === $count - 1) {
                $item['budget_amount'] = round($totalValue - $runningSum, 2);
                $item['forecast_amount'] = round($totalValue - $runningSum, 2);
            } else {
                $item['budget_amount'] = round($item['budget_amount'], 2);
                $item['forecast_amount'] = round($item['forecast_amount'], 2);
                $runningSum += $item['budget_amount'];
            }
        }

        $salesPlan->revenue_distribution_planning = $distribution;
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
