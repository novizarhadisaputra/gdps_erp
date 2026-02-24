<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\ContractType;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesPlan;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\IndustrialSector;
use Modules\MasterData\Models\ProjectType;
use Modules\MasterData\Models\RevenueSegment;
use Modules\MasterData\Models\SkillCategory;
use Modules\Project\Models\Project;
use Tests\TestCase;

class SalesPlanTest extends TestCase
{
    use RefreshDatabase;

    protected $revenueSegment;

    protected $industrialSector;

    protected $projectType;

    protected $skillCategory;

    protected $customer;

    protected $lead;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup basic master data
        $this->revenueSegment = RevenueSegment::create(['name' => 'Aviation', 'code' => 'AV']);
        $this->industrialSector = IndustrialSector::create(['name' => 'Airlines', 'code' => 'AIR']);
        $this->projectType = ProjectType::create(['name' => 'Headcount', 'code' => 'HC']);
        $this->skillCategory = SkillCategory::create(['name' => 'Low Skill', 'code' => 'LS']);
        $this->customer = Customer::create(['name' => 'Garuda Indonesia']);

        $this->lead = Lead::create([
            'title' => 'Test Lead',
            'customer_id' => $this->customer->id,
            'status' => 'approach',
        ]);
    }

    public function test_sales_plan_automatically_creates_monthly_breakdowns(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'revenue_segment_id' => $this->revenueSegment->id,
            'industrial_sector_id' => $this->industrialSector->id,
            'project_type_id' => $this->projectType->id,
            'skill_category_id' => $this->skillCategory->id,
            'estimated_value' => 12000000,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'priority_level' => 1,
            'confidence_level' => 'optimistic',
            'revenue_distribution_planning' => [
                ['month' => 'January 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'February 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'March 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'April 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'May 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'June 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'July 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'August 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'September 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'October 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'November 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
                ['month' => 'December 2026', 'budget_amount' => 1000000, 'forecast_amount' => 1000000, 'actual_amount' => 0],
            ],
        ]);

        $this->assertDatabaseCount('sales_plan_monthlies', 12);
        $this->assertEquals(1000000, $salesPlan->monthlyBreakdowns()->first()->budget_amount);
        $this->assertEquals(1000000, $salesPlan->monthlyBreakdowns()->first()->forecast_amount);
    }

    public function test_proposal_syncs_number_to_sales_plan(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'confidence_level' => 'moderate',
            'priority_level' => 2,
        ]);

        $proposal = Proposal::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'proposal_number' => 'PROP-001',
            'amount' => 1000000,
            'status' => 'submitted',
        ]);

        $salesPlan->refresh();
        $this->assertStringContainsString('PROP-001', $salesPlan->proposal_number);
    }

    public function test_project_syncs_code_to_sales_plan(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'confidence_level' => 'moderate',
            'priority_level' => 2,
        ]);

        $project = Project::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'code' => 'PROJ-999',
            'name' => 'Project Alpha',
            'status' => 'active',
        ]);

        $salesPlan->refresh();
        $this->assertEquals('PROJ-999', $salesPlan->project_code);
    }

    public function test_contract_creation_auto_links_to_sales_plan(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'confidence_level' => 'moderate',
            'priority_level' => 2,
        ]);

        $proposal = Proposal::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'proposal_number' => 'PROP-XYZ',
            'amount' => 1000000,
            'status' => 'submitted',
        ]);

        $contract = Contract::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'proposal_id' => $proposal->id,
            'contract_number' => 'WO-001',
            'type' => ContractType::WorkOrder,
            'status' => 'draft',
        ]);

        $salesPlan->refresh();
        $this->assertEquals($contract->id, $salesPlan->work_order_id);
    }

    public function test_sales_plan_observer_daily_proration_with_cutoff(): void
    {
        // 2026-02-10 to 2026-04-10
        // Cutoff: 25
        // Cycle 1: Feb 10 - Feb 25 (16 days)
        // Cycle 2: Feb 26 - March 25 (28 days)
        // Cycle 3: March 26 - April 10 (16 days)
        // Total: 60 days

        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'project_type_id' => $this->projectType->id,
            'estimated_value' => 60000000,
            'start_date' => '2026-02-10',
            'end_date' => '2026-04-10',
            'cutoff_day' => 25,
            'proration_method' => \Modules\CRM\Enums\ProrationMethod::Daily,
            'top_days' => 45, // Should not affect distribution month
        ]);

        $distribution = $salesPlan->revenue_distribution_planning;

        $this->assertCount(3, $distribution);

        // February 2026 (Cycle ends Feb 25)
        $this->assertEquals('February 2026', $distribution[0]['month']);
        $this->assertEquals(16000000.0, (float) $distribution[0]['budget_amount']);

        // March 2026 (Cycle ends March 25)
        $this->assertEquals('March 2026', $distribution[1]['month']);
        $this->assertEquals(28000000.0, (float) $distribution[1]['budget_amount']);

        // April 2026 (Cycle ends April 10)
        $this->assertEquals('April 2026', $distribution[2]['month']);
        $this->assertEquals(16000000.0, (float) $distribution[2]['budget_amount']);

        // Verify monthly breakdowns table
        $this->assertDatabaseHas('sales_plan_monthlies', [
            'sales_plan_id' => $salesPlan->id,
            'year' => 2026,
            'month' => 2,
            'budget_amount' => 16000000,
        ]);
        $this->assertDatabaseHas('sales_plan_monthlies', [
            'sales_plan_id' => $salesPlan->id,
            'year' => 2026,
            'month' => 3,
            'budget_amount' => 28000000,
        ]);
        $this->assertDatabaseHas('sales_plan_monthlies', [
            'sales_plan_id' => $salesPlan->id,
            'year' => 2026,
            'month' => 4,
            'budget_amount' => 16000000,
        ]);
    }

    public function test_sales_plan_observer_equal_proration(): void
    {
        $salesPlan = SalesPlan::create([
            'lead_id' => $this->lead->id,
            'project_type_id' => $this->projectType->id,
            'estimated_value' => 30000000,
            'start_date' => '2026-02-10',
            'end_date' => '2026-04-10',
            'cutoff_day' => 25,
            'proration_method' => \Modules\CRM\Enums\ProrationMethod::Equal,
        ]);

        $distribution = $salesPlan->revenue_distribution_planning;

        // Feb, Mar, Apr (3 months)
        $this->assertCount(3, $distribution);
        foreach ($distribution as $item) {
            $this->assertEquals(10000000.0, (float) $item['budget_amount']);
        }
    }
}
