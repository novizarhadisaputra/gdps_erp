<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Services\SignatureService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SignatureServiceEligibilityTest extends TestCase
{
    use RefreshDatabase;

    protected SignatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SignatureService::class);
    }

    protected function createRole(string $name): Role
    {
        $role = new class extends Role
        {
            public $incrementing = false;

            protected $keyType = 'string';
        };
        $role->id = \Illuminate\Support\Str::uuid()->toString();
        $role->name = $name;
        $role->guard_name = 'web';
        $role->save();

        return $role;
    }

    public function test_it_correctly_identifies_eligible_approver_by_role_uuid(): void
    {
        $role = $this->createRole('VP Finance');
        $user = User::factory()->create();
        $user->assignRole($role);

        $rule = ApprovalRule::create([
            'resource_type' => 'SomeModel',
            'approver_type' => 'Role',
            'approver_role' => [$role->id],
            'is_active' => true,
        ]);

        $this->assertTrue($this->service->isEligibleApprover($rule, $user));
        $this->assertCount(1, $this->service->getEligibleUsers($rule));
    }

    public function test_it_correctly_identifies_eligible_approver_by_role_name(): void
    {
        $role = $this->createRole('VP Finance');
        $user = User::factory()->create();
        $user->assignRole($role);

        $rule = ApprovalRule::create([
            'resource_type' => 'SomeModel',
            'approver_type' => 'Role',
            'approver_role' => ['VP Finance'],
            'is_active' => true,
        ]);

        $this->assertTrue($this->service->isEligibleApprover($rule, $user));
        $this->assertCount(1, $this->service->getEligibleUsers($rule));
    }

    public function test_it_denies_ineligible_approver(): void
    {
        $role = $this->createRole('VP Finance');
        $otherRole = $this->createRole('VP Operations');
        $user = User::factory()->create();
        $user->assignRole($role);

        $rule = ApprovalRule::create([
            'resource_type' => 'SomeModel',
            'approver_type' => 'Role',
            'approver_role' => ['VP Operations'],
            'is_active' => true,
        ]);

        $this->assertFalse($this->service->isEligibleApprover($rule, $user));
        $this->assertCount(0, $this->service->getEligibleUsers($rule));
    }
}
