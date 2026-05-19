<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\Unit;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class UnitApprovalRuleTest extends TestCase
{
    use RefreshDatabase;

    protected SignatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SignatureService;
    }

    public function test_is_eligible_approver_with_unit_direct_external_id_match(): void
    {
        $rule = new ApprovalRule;
        $rule->approver_type = 'Unit';
        $rule->approver_unit_id = ['10000005']; // Stores target unit_id (external_id or UUID)

        $user = User::factory()->create([
            'unit_id' => '10000005',
        ]);

        $this->assertTrue($this->service->isEligibleApprover($rule, $user));
    }

    public function test_is_eligible_approver_with_unit_uuid_match_via_relationship(): void
    {
        // 1. Create a Unit with a specific UUID and external_id
        $unit = Unit::create([
            'id' => '019e3d80-de9e-7053-8cff-0d6423fd9b14',
            'external_id' => '9500159',
            'code' => 'UB',
            'name' => 'Business Support',
            'is_active' => true,
        ]);

        // 2. Create a rule targeting the unit's UUID
        $rule = new ApprovalRule;
        $rule->approver_type = 'Unit';
        $rule->approver_unit_id = [$unit->id];

        // 3. Create a user belonging to that unit via external_id
        $user = User::factory()->create([
            'unit_id' => '9500159',
        ]);

        // 4. Verify user is recognized as eligible because unit UUID matches through the relationship lookup
        $this->assertTrue($this->service->isEligibleApprover($rule, $user));
    }

    public function test_get_eligible_users_for_unit_rule(): void
    {
        // 1. Create target unit
        $unit = Unit::create([
            'id' => '019e3d80-de98-7121-8235-b1acf56465ad',
            'external_id' => '9500160',
            'code' => 'UF',
            'name' => 'Corporate Finance',
            'is_active' => true,
        ]);

        // 2. Create users
        $eligibleUser = User::factory()->create([
            'unit_id' => '9500160',
        ]);

        $otherUser = User::factory()->create([
            'unit_id' => '9999999',
        ]);

        // 3. Create rule targeting unit's UUID
        $rule = new ApprovalRule;
        $rule->approver_type = 'Unit';
        $rule->approver_unit_id = [$unit->id];

        // 4. Query eligible users
        $users = $this->service->getEligibleUsers($rule);

        $this->assertCount(1, $users);
        $this->assertEquals($eligibleUser->id, $users->first()->id);
    }
}
