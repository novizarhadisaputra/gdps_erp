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
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectGenerationWorkflowTest extends TestCase
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
        $projectType = \Modules\MasterData\Models\ProjectType::factory()->create(['code' => 'TYP']);

        // 2. Setup PA without tax
        /** @var ProfitabilityAnalysis $pa */
        $pa = ProfitabilityAnalysis::factory()->create([
            'customer_id' => $customer->id,
            'work_scheme_id' => $workScheme->id,
            'product_cluster_id' => $productCluster->id,
            'project_area_id' => $projectArea->id,
            'project_type_id' => $projectType->id,
            'tax_id' => $tax->id,
            'status' => 'approved',
            'revenue_per_month' => 66000000,
            'direct_cost' => 50000000,
            'management_fee' => 5000000,
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $service = new ProjectGenerationService;

        // 3. Generate Project
        $project = $service->generateFromPA($pa);

        // 4. Assertions
        $this->assertInstanceOf(Project::class, $project);
        $this->assertStringNotContainsString('XXX', $project->number);
        $this->assertStringNotContainsString('XX', $project->number);
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
            'project_type_id' => \Modules\MasterData\Models\ProjectType::factory()->create(['code' => 'TYP'])->id,
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
        $service = new ProjectGenerationService;
        $project = $service->generateFromPA($pa);

        $this->assertInstanceOf(Project::class, $project);
        $this->assertStringNotContainsString('XXX', $project->number);
        $this->assertEquals($pa->start_date->format('Y-m-d'), $project->information->start_date->format('Y-m-d'));
    }

    public function test_project_inherits_all_fields_and_maps_employees(): void
    {
        // 1. Setup required relations
        $customer = Customer::factory()->create(['code' => 'CUS']);
        $productCluster = ProductCluster::factory()->create(['code' => 'CLS']);
        $projectArea = ProjectArea::factory()->create(['code' => 'ARE']);
        $tax = Tax::factory()->create(['code' => 'TX']);
        $projectType = \Modules\MasterData\Models\ProjectType::factory()->create(['name' => 'Test Type', 'code' => 'TYP']);
        $workScheme = WorkScheme::factory()->create();

        // 2 Setup Users and Employees for mapping
        $costingUser = \App\Models\User::factory()->create(['email' => 'costing@example.com']);
        $salesUser = \App\Models\User::factory()->create(['email' => 'sales@example.com']);

        $costingEmployee = Employee::factory()->create(['email' => 'costing@example.com']);
        $salesEmployee = Employee::factory()->create(['email' => 'sales@example.com']);

        // 3. Setup Lead
        $lead = Lead::factory()->create([
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
            'product_cluster_id' => $productCluster->id,
            'tax_id' => $tax->id,
            'project_type_id' => $projectType->id,
            'pic_costing_id' => $costingUser->id,
            'user_id' => $salesUser->id,
        ]);

        // 4. Create PA from Lead (via GI for realistic flow)
        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'work_scheme_id' => $workScheme->id,
        ]);
        $pa = $gi->toProfitabilityAnalysis();
        $pa->update(['status' => 'approved']);

        // 5. Generate Project
        $service = new ProjectGenerationService;
        $project = $service->generateFromPA($pa);

        // 6. Assertions
        $this->assertEquals($workScheme->id, $project->work_scheme_id, 'Work Scheme ID should be propagated');
        $this->assertEquals($projectType->id, $project->project_type_id, 'Project Type ID should be propagated');
        $this->assertEquals($costingEmployee->id, $project->oprep_id, 'OPREP ID should be mapped from User to Employee via email');
        $this->assertEquals($salesEmployee->id, $project->ams_id, 'AMS ID should be mapped from User to Employee via email');

        // Assert ProjectInformation sync
        $this->assertEquals($projectType->id, $project->information->project_type_id);
        $this->assertEquals($costingEmployee->id, $project->information->oprep_id);
        $this->assertEquals($salesEmployee->id, $project->information->ams_id);
    }
}
