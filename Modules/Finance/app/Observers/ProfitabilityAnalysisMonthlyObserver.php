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

        $this->calculateProfitability($monthly);
    }

    public function saving(ProfitabilityAnalysisMonthly $monthly): void
    {
        $this->calculateProfitability($monthly);
    }

    /**
     * Recalculate profitability metrics based on actual revenue and cost.
     */
    protected function calculateProfitability(ProfitabilityAnalysisMonthly $monthly): void
    {
        $monthly->actual_revenue = (float) $monthly->actual_revenue;
        $monthly->actual_cost = (float) $monthly->actual_cost;
        $monthly->target_revenue = (float) $monthly->target_revenue;

        // 1. Calculate Profit & Margin
        $monthly->actual_net_profit = $monthly->actual_revenue - $monthly->actual_cost;
        
        if ($monthly->actual_revenue > 0) {
            $monthly->actual_margin_percentage = ($monthly->actual_net_profit / $monthly->actual_revenue) * 100;
        } else {
            $monthly->actual_margin_percentage = 0;
        }

        // 2. Calculate Variances
        $monthly->variance_revenue = $monthly->actual_revenue - $monthly->target_revenue;

        // Calculate Variance Profit based on baseline margin percentage if available
        $pa = $monthly->profitabilityAnalysis;
        if ($pa) {
            $baselineMarginRate = (float) $pa->net_profit_margin / 100;
            if ($baselineMarginRate == 0 && (float) $pa->revenue_per_month > 0) {
                $baselineMarginRate = (float) $pa->net_profit / (float) $pa->revenue_per_month;
            }

            $expectedProfit = $monthly->target_revenue * $baselineMarginRate;
            $monthly->variance_profit = $monthly->actual_net_profit - $expectedProfit;
        }
    }

    public function saved(ProfitabilityAnalysisMonthly $monthly): void
    {
        $this->syncToSalesPlan($monthly);
    }

    public function updated(ProfitabilityAnalysisMonthly $monthly): void
    {
        // 1. Log Forecast Revenue changes
        if ($monthly->wasChanged('forecast_revenue')) {
            $monthly->logs()->create([
                'user_id' => auth()->id(),
                'field_name' => 'forecast_revenue',
                'old_value' => $monthly->getOriginal('forecast_revenue'),
                'new_value' => $monthly->forecast_revenue,
                'delta' => (float) $monthly->forecast_revenue - (float) $monthly->getOriginal('forecast_revenue'),
            ]);
        }

        // 2. Log Actual Revenue changes
        if ($monthly->wasChanged('actual_revenue')) {
            $monthly->logs()->create([
                'user_id' => auth()->id(),
                'field_name' => 'actual_revenue',
                'old_value' => $monthly->getOriginal('actual_revenue'),
                'new_value' => $monthly->actual_revenue,
                'delta' => (float) $monthly->actual_revenue - (float) $monthly->getOriginal('actual_revenue'),
            ]);
        }
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
