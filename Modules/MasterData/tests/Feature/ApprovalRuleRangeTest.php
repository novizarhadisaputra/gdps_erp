<?php

namespace Modules\MasterData\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class ApprovalRuleRangeTest extends TestCase
{
    use RefreshDatabase;

    protected SignatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SignatureService::class);
    }

    public function test_it_can_evaluate_between_operator_correctly(): void
    {
        // 1. Create a rule for MoA with 'between' operator
        $rule = ApprovalRule::create([
            'resource_type' => MinutesOfAgreement::class,
            'criteria_field' => 'amount',
            'operator' => 'between',
            'value' => 1000,
            'max_value' => 5000,
            'approver_type' => 'Role',
            'approver_role' => ['admin'],
            'signature_type' => 'approval',
            'order' => 1,
            'is_active' => true,
        ]);

        // 2. Mock MoA with amount within range
        $moaWithin = new MinutesOfAgreement(['amount' => 3000]);
        $approversWithin = $this->service->getRequiredApprovers($moaWithin);
        $this->assertCount(1, $approversWithin);

        // 3. Mock MoA with amount at lower bound
        $moaLower = new MinutesOfAgreement(['amount' => 1000]);
        $approversLower = $this->service->getRequiredApprovers($moaLower);
        $this->assertCount(1, $approversLower);

        // 4. Mock MoA with amount at upper bound
        $moaUpper = new MinutesOfAgreement(['amount' => 5000]);
        $approversUpper = $this->service->getRequiredApprovers($moaUpper);
        $this->assertCount(1, $approversUpper);

        // 5. Mock MoA with amount outside range (too low)
        $moaTooLow = new MinutesOfAgreement(['amount' => 500]);
        $approversTooLow = $this->service->getRequiredApprovers($moaTooLow);
        $this->assertCount(0, $approversTooLow);

        // 6. Mock MoA with amount outside range (too high)
        $moaTooHigh = new MinutesOfAgreement(['amount' => 6000]);
        $approversTooHigh = $this->service->getRequiredApprovers($moaTooHigh);
        $this->assertCount(0, $approversTooHigh);
    }

    public function test_it_can_evaluate_multiple_conditions_correctly(): void
    {
        // Create a rule with multiple conditions (AND logic)
        // Revenue > 1000 AND Margin > 10%
        $rule = ApprovalRule::create([
            'resource_type' => \Modules\Finance\Models\ProfitabilityAnalysis::class,
            'conditions' => [
                [
                    'field' => 'revenue_per_month',
                    'operator' => '>',
                    'value' => 1000,
                ],
                [
                    'field' => 'margin_percentage',
                    'operator' => '>',
                    'value' => 10,
                ],
            ],
            'approver_type' => 'Role',
            'approver_role' => ['admin'],
            'signature_type' => 'approval',
            'order' => 1,
            'is_active' => true,
        ]);

        $pa = new \Modules\Finance\Models\ProfitabilityAnalysis;

        // 1. Both met
        $pa->revenue_per_month = 2000;
        $pa->margin_percentage = 15;
        $this->assertCount(1, $this->service->getRequiredApprovers($pa));

        // 2. Only one met (Revenue)
        $pa->revenue_per_month = 2000;
        $pa->margin_percentage = 5;
        $this->assertCount(0, $this->service->getRequiredApprovers($pa));

        // 3. Only one met (Margin)
        $pa->revenue_per_month = 500;
        $pa->margin_percentage = 15;
        $this->assertCount(0, $this->service->getRequiredApprovers($pa));

        // 4. None met
        $pa->revenue_per_month = 500;
        $pa->margin_percentage = 5;
        $this->assertCount(0, $this->service->getRequiredApprovers($pa));
    }
}
