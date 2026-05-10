<?php

namespace Modules\MasterData\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Enums\ApprovalSignatureType;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class ApprovalRuleRelationshipTest extends TestCase
{
    use RefreshDatabase;

    protected SignatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SignatureService::class);
    }

    public function test_it_can_evaluate_relationship_rule_correctly(): void
    {
        // 1. Create a Product Cluster
        $cluster = ProductCluster::create([
            'name' => 'Test Cluster',
            'code' => 'TCL',
            'is_active' => true,
        ]);

        $otherCluster = ProductCluster::create([
            'name' => 'Other Cluster',
            'code' => 'OCL',
            'is_active' => true,
        ]);

        // 2. Create a rule for Profitability Analysis with product_cluster_id criteria
        $rule = ApprovalRule::create([
            'resource_type' => ProfitabilityAnalysis::class,
            'conditions' => [
                [
                    'field' => 'product_cluster_id',
                    'operator' => '=',
                    'value' => $cluster->id,
                ],
            ],
            'approver_type' => 'Role',
            'approver_role' => ['admin'],
            'signature_type' => ApprovalSignatureType::Approver,
            'order' => 1,
            'is_active' => true,
        ]);

        // 3. Mock PA with matching cluster
        $paMatching = new ProfitabilityAnalysis(['product_cluster_id' => $cluster->id]);
        $approversMatching = $this->service->getRequiredApprovers($paMatching);
        $this->assertCount(1, $approversMatching);

        // 4. Mock PA with non-matching cluster
        $paNotMatching = new ProfitabilityAnalysis(['product_cluster_id' => $otherCluster->id]);
        $approversNotMatching = $this->service->getRequiredApprovers($paNotMatching);
        $this->assertCount(0, $approversNotMatching);
    }
}
