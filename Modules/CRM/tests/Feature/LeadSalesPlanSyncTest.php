<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Models\RevenueSegment;
use Tests\TestCase;

class LeadSalesPlanSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_plan_is_automatically_created_when_lead_status_is_approach(): void
    {
        $revenueSegment = RevenueSegment::create([
            'name' => 'Gov',
            'code' => 'RS-001',
            'is_active' => true,
        ]);

        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => \Modules\MasterData\Models\Customer::factory()->create()->id,
            'status' => LeadStatus::Lead,
            'revenue_segment_id' => $revenueSegment->id,
            'estimated_amount' => 1000000,
        ]);

        $this->assertDatabaseMissing('sales_plans', [
            'lead_id' => $lead->id,
        ]);

        // Move to Approach
        $lead->update(['status' => LeadStatus::Approach]);

        $this->assertDatabaseHas('sales_plans', [
            'lead_id' => $lead->id,
            'revenue_segment_id' => $revenueSegment->id,
            'estimated_value' => 1000000,
        ]);
    }

    public function test_lead_categorization_syncs_to_existing_sales_plan(): void
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => \Modules\MasterData\Models\Customer::factory()->create()->id,
            'estimated_amount' => 1000000,
        ]);

        // Trigger SalesPlan creation
        $lead->update(['status' => LeadStatus::Approach]);

        $salesPlan = SalesPlan::where('lead_id', $lead->id)->first();
        $this->assertNotNull($salesPlan);

        $newSegment = RevenueSegment::create([
            'name' => 'Private',
            'code' => 'RS-002',
            'is_active' => true,
        ]);

        $lead->update([
            'revenue_segment_id' => $newSegment->id,
            'estimated_amount' => 5000000,
        ]);

        $this->assertEquals($newSegment->id, $salesPlan->refresh()->revenue_segment_id);
        $this->assertEquals(5000000, $salesPlan->estimated_value);
    }

    public function test_proposal_amount_syncs_to_sales_plan_value(): void
    {
        $lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => \Modules\MasterData\Models\Customer::factory()->create()->id,
        ]);

        // Trigger SalesPlan creation
        $lead->update(['status' => LeadStatus::Approach]);

        $salesPlan = SalesPlan::where('lead_id', $lead->id)->first();
        $this->assertNotNull($salesPlan);

        $proposal = Proposal::create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
            'proposal_number' => 'PROP-2026-001',
            'amount' => 7500000,
            'status' => 'draft',
        ]);

        $this->assertEquals(7500000, $salesPlan->refresh()->estimated_value);
    }
}
