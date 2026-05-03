<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\SalesPlanMonthly;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;
use Tests\TestCase;

class SalesPlanFinanceSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_plan_monthly_budget_updates_finance_pa_target_revenue(): void
    {
        // 1. Setup Lead and Sales Plan
        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => \Modules\CRM\Models\Customer::factory()->create()->id,
            'status' => LeadStatus::Approach,
        ]);

        $salesPlan = $lead->refresh()->salesPlan;
        $this->assertNotNull($salesPlan);

        // 2. Setup Profitability Analysis in Finance
        $pa = ProfitabilityAnalysis::create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
            'status' => ProfitabilityAnalysisStatus::Draft,
            'year' => date('Y'),
            'revenue_per_month' => 1000000,
        ]);

        $monthName = 'January';
        $monthInt = 1;

        $paMonthly = ProfitabilityAnalysisMonthly::create([
            'profitability_analysis_id' => $pa->id,
            'month' => $monthName,
            'year' => date('Y'),
            'target_revenue' => 0,
        ]);

        // 3. Create Sales Plan Monthly in CRM
        $salesPlanMonthly = SalesPlanMonthly::create([
            'sales_plan_id' => $salesPlan->id,
            'year' => date('Y'),
            'month' => $monthInt,
            'budget_amount' => 5000000,
        ]);

        // 4. Verify PA Monthly target revenue was updated by SalesPlanMonthlyObserver
        $this->assertEquals(5000000, $paMonthly->refresh()->target_revenue);

        // 5. Update Budget in CRM
        $salesPlanMonthly->update([
            'budget_amount' => 7500000,
        ]);

        // 6. Verify PA Monthly reflects the update
        $this->assertEquals(7500000, $paMonthly->refresh()->target_revenue);
    }

    public function test_sales_plan_monthly_does_not_update_approved_pa(): void
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => \Modules\CRM\Models\Customer::factory()->create()->id,
            'status' => LeadStatus::Approach,
        ]);

        $salesPlan = $lead->refresh()->salesPlan;

        // Approved PA
        $pa = ProfitabilityAnalysis::create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
            'status' => ProfitabilityAnalysisStatus::Approved,
            'year' => date('Y'),
        ]);

        $paMonthly = ProfitabilityAnalysisMonthly::create([
            'profitability_analysis_id' => $pa->id,
            'month' => 'January',
            'year' => date('Y'),
            'target_revenue' => 1000000,
        ]);

        // Create Sales Plan Monthly
        SalesPlanMonthly::create([
            'sales_plan_id' => $salesPlan->id,
            'year' => date('Y'),
            'month' => 1,
            'budget_amount' => 5000000,
        ]);

        // Should NOT update since PA is Approved
        $this->assertEquals(1000000, $paMonthly->refresh()->target_revenue);
    }
}
