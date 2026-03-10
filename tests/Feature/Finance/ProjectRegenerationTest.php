<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\ContractStatus;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectRegenerationTest extends TestCase
{
    use RefreshDatabase;

    protected ProjectGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProjectGenerationService;
    }

    public function test_it_maintains_project_identity_and_number_when_regenerating_from_new_pa(): void
    {
        // 1. Setup: Customer, Lead, and initial PA
        $customer = Customer::factory()->create(['code' => 'GIA']);
        $lead = Lead::factory()->create(['customer_id' => $customer->id]);
        $contract = Contract::factory()->create([
            'lead_id' => $lead->id,
            'status' => ContractStatus::Active,
        ]);

        $pa1 = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'project_number' => null,
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory(),
        ]);

        // 2. Generate initial project
        $project1 = $this->service->generateFromPA($pa1);

        $this->assertEquals(1, $project1->project_number);
        $this->assertEquals($contract->id, $project1->contract_id);
        $initialCode = $project1->code;

        // 3. Setup: New PA for the same lead/contract
        $pa2 = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
        ]);

        // 4. Regenerate project from PA 2
        $project2 = $this->service->generateFromPA($pa2);

        // Assertions
        $this->assertEquals($project1->id, $project2->id, 'Project ID should be the same (updated, not recreated)');
        $this->assertEquals(1, $project2->project_number, 'Project number should remain stable');
        $this->assertEquals($pa2->id, $project2->profitability_analysis_id, 'PA ID should be updated to the latest one');
        $this->assertEquals($initialCode, $project2->code, 'Project Code should remain the same if parameters are equal');
    }

    public function test_it_increments_sequence_when_generating_from_new_contract_renewal(): void
    {
        // 0. Shared setup
        $workScheme = \Modules\MasterData\Models\WorkScheme::factory()->create();
        $cluster = \Modules\MasterData\Models\ProductCluster::factory()->create();
        $area = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $tax = \Modules\MasterData\Models\Tax::factory()->create();

        // 1. Setup: First Project
        $customer = Customer::factory()->create(['code' => 'GIA']);
        $lead1 = Lead::factory()->create(['customer_id' => $customer->id]);
        $contract1 = Contract::factory()->create(['lead_id' => $lead1->id, 'status' => ContractStatus::Active]);
        $pa1 = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead1->id,
            'customer_id' => $customer->id,
            'project_number' => null,
            'work_scheme_id' => $workScheme->id,
            'product_cluster_id' => $cluster->id,
            'project_area_id' => $area->id,
            'tax_id' => $tax->id,
        ]);

        $project1 = $this->service->generateFromPA($pa1);
        $this->assertEquals(1, $project1->project_number);

        // 2. Setup: Renewal (New Contract)
        $lead2 = Lead::factory()->create(['customer_id' => $customer->id]);
        $contract2 = Contract::factory()->create(['lead_id' => $lead2->id, 'status' => ContractStatus::Active]);
        $pa2 = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead2->id,
            'customer_id' => $customer->id,
            'project_number' => null,
            'work_scheme_id' => $workScheme->id,
            'product_cluster_id' => $cluster->id,
            'project_area_id' => $area->id,
            'tax_id' => $tax->id,
        ]);

        // 3. Generate second project
        $project2 = $this->service->generateFromPA($pa2);

        // Assertions
        $this->assertNotEquals($project1->id, $project2->id);
        $this->assertEquals(2, $project2->project_number, 'Project number should increment for new contract');
        $this->assertStringContainsString('01', $project1->code);
        $this->assertStringContainsString('02', $project2->code);
    }
}
