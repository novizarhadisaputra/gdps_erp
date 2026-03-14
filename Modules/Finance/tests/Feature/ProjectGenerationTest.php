<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\SalesPlan;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_generate_project_from_pa_without_tax(): void
    {
        // 1. Setup required relations with codes
        $customer = Customer::factory()->create(['name' => 'Test Customer', 'code' => 'CUS']);
        $workScheme = WorkScheme::factory()->create();
        $productCluster = ProductCluster::factory()->create(['code' => 'CLS']);
        $projectArea = ProjectArea::factory()->create(['code' => 'ARE']);
        $tax = Tax::factory()->create(['code' => 'TX']);

        // 2. Setup PA without tax (it will use default tax in service but we need a tax_id for project code validation)
        /** @var ProfitabilityAnalysis $pa */
        $pa = ProfitabilityAnalysis::factory()->create([
            'customer_id' => $customer->id,
            'work_scheme_id' => $workScheme->id,
            'product_cluster_id' => $productCluster->id,
            'project_area_id' => $projectArea->id,
            'tax_id' => $tax->id,
            'status' => 'approved',
            'revenue_per_month' => 66000000,
            'direct_cost' => 50000000,
            'management_fee' => 5000000,
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $service = new ProjectGenerationService();

        // 3. Generate Project
        $project = $service->generateFromPA($pa);

        // 4. Assertions
        $this->assertInstanceOf(Project::class, $project);
        $this->assertStringNotContainsString('XXX', $project->code);
        $this->assertStringNotContainsString('XX', $project->code);
        $this->assertEquals(11.00, (float) $project->information->ppn_percentage);
        $this->assertEquals(66000000.00, (float) $project->information->revenue_per_month);
    }

    public function test_pa_created_from_gi_inherits_sales_plan_data(): void
    {
        // 1. Setup Lead, SalesPlan with all required data for code
        $customer = Customer::factory()->create(['code' => 'CUS']);
        $projectArea = ProjectArea::factory()->create(['code' => 'ARE']);
        $productCluster = ProductCluster::factory()->create(['code' => 'CLS']);
        $tax = Tax::factory()->create(['code' => 'TX']);
        
        $lead = Lead::factory()->create([
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
            'product_cluster_id' => $productCluster->id,
            'tax_id' => $tax->id,
        ]);
        
        $paymentTerm = \Modules\MasterData\Models\PaymentTerm::factory()->create();
        
        $salesPlan = SalesPlan::factory()->create([
            'lead_id' => $lead->id,
            'payment_term_id' => $paymentTerm->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'sales_plan_id' => $salesPlan->id,
        ]);

        // 2. Performance action (GI -> PA)
        $pa = $gi->toProfitabilityAnalysis();

        // 3. Assertions
        $this->assertEquals($paymentTerm->id, $pa->payment_term_id);
        $this->assertEquals('2026-01-01', $pa->start_date->format('Y-m-d'));
        $this->assertEquals('2026-12-31', $pa->end_date->format('Y-m-d'));
        
        // 4. Test Project Generation from this PA
        $pa->update(['status' => 'approved']);
        $service = new ProjectGenerationService();
        $project = $service->generateFromPA($pa);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertStringNotContainsString('XXX', $project->code);
        $this->assertEquals($pa->start_date->format('Y-m-d'), $project->information->start_date->format('Y-m-d'));
    }
}
