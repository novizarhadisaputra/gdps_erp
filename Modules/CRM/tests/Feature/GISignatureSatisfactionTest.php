<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\GeneralInformationStatus;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Services\SignatureService;
use Tests\TestCase;

class GISignatureSatisfactionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    protected function createRole(string $name): Role
    {
        $role = new class extends Role {
            public $incrementing = false;
            protected $keyType = 'string';
        };
        $role->id = (string) \Illuminate\Support\Str::uuid();
        $role->name = $name;
        $role->guard_name = 'web';
        $role->save();
        return $role;
    }

    public function test_gi_signature_satisfaction_works_with_resolving_role_names(): void
    {
        // 1. Setup Role and User
        $role = $this->createRole('VP Business Support');
        $user = User::factory()->create();
        $user->assignRole($role);

        // 2. Setup GI with a rule that uses the Role UUID
        $lead = Lead::factory()->create();
        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'status' => GeneralInformationStatus::Submitted,
            'rr_status' => 'pending'
        ]);

        $rule = ApprovalRule::create([
            'resource_type' => GeneralInformation::class,
            'approver_type' => 'Role',
            'approver_role' => [$role->id],
            'signature_type' => 'Approver',
            'is_active' => true,
            'order' => 1
        ]);

        // 3. Add signature recording the role NAME (standard behavior in GDPS)
        $gi->addSignature($user, 'Approver', 'VP Business Support');

        // 4. Verify satisfaction
        $this->assertTrue($gi->isRuleSatisfied($rule), 'Rule should be satisfied by resolving name to UUID');
        $this->assertTrue($gi->isTypeApproved('Approver'));
    }

    public function test_gi_is_fully_approved_only_when_signatures_and_rr_are_ready(): void
    {
        $role = $this->createRole('Approver Role');
        $user = User::factory()->create();
        $user->assignRole($role);

        $lead = Lead::factory()->create();
        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'status' => GeneralInformationStatus::Submitted,
            'rr_status' => 'pending'
        ]);

        ApprovalRule::create([
            'resource_type' => GeneralInformation::class,
            'approver_type' => 'Role',
            'approver_role' => [$role->id],
            'signature_type' => 'Approver',
            'is_active' => true,
        ]);

        // Scenario 1: Signed but RR pending
        $gi->addSignature($user, 'Approver', 'Approver Role');
        $this->assertFalse($gi->isFullyApproved(), 'Should NOT be fully approved if RR is pending');

        // Scenario 2: Signed and RR approved
        $gi->rr_status = 'approved';
        $gi->save();
        $this->assertTrue($gi->isFullyApproved(), 'Should be fully approved when both are ready');
    }
}
