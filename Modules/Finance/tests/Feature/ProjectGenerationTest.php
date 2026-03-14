<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Modules\Finance\Classes\ProjectGenerationService;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectGenerationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_generate_project_from_pa_without_tax(): void
    {
        // 1. Setup required relations
        $customer = \Modules\CRM\Models\Customer::factory()->create(['name' => 'Test Customer']);
        $workScheme = \Modules\MasterData\Models\WorkScheme::factory()->create();
        $productCluster = \Modules\MasterData\Models\ProductCluster::factory()->create();

        // 2. Setup PA without tax
        /** @var ProfitabilityAnalysis $pa */
        $pa = ProfitabilityAnalysis::factory()->create([
            'customer_id' => $customer->id,
            'work_scheme_id' => $workScheme->id,
            'product_cluster_id' => $productCluster->id,
            'tax_id' => null,
            'status' => 'approved',
            'revenue_per_month' => 66000000,
            'direct_cost' => 50000000,
            'management_fee' => 5000000,
        ]);

        $service = new ProjectGenerationService();

        // 3. Generate Project
        $project = $service->generateFromPA($pa);

        // 4. Assertions
        $this->assertInstanceOf(Project::class, $project);
        $this->assertEquals(11.00, (float) $project->information->ppn_percentage); // Should use default 11.00
        $this->assertEquals(66000000.00, (float) $project->information->revenue_per_month);
    }
}
