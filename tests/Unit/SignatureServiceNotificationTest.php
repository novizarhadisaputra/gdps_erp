<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Services\SignatureService;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SignatureServiceNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected SignatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SignatureService;
    }

    public function test_get_eligible_users_returns_empty_on_empty_role_identifiers(): void
    {
        // 1. Manually insert a role to avoid UUID issues with Eloquent in test
        $roleId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'sales',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        // Manual attach to avoid Role model issues
        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_id' => $user->id,
            'model_type' => User::class,
        ]);

        // 2. Create a rule (Using an object that matches ApprovalRule properties)
        $rule = new ApprovalRule;
        $rule->approver_type = 'Role';
        $rule->approver_role = []; // EMPTY

        // 3. Verify it returns empty collection
        $users = $this->service->getEligibleUsers($rule);

        $this->assertCount(0, $users);
    }

    public function test_get_eligible_users_returns_correct_users_with_valid_role_identifiers(): void
    {
        // 1. Create a user with a specific role via manual DB insertion
        $roleId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'approver',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::factory()->create();
        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
            'role_id' => $roleId,
            'model_id' => $user->id,
            'model_type' => User::class,
        ]);

        // 2. Create another user with a different role
        $otherRoleId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $otherRoleId,
            'name' => 'sales',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherUser = User::factory()->create();
        \Illuminate\Support\Facades\DB::table('model_has_roles')->insert([
            'role_id' => $otherRoleId,
            'model_id' => $otherUser->id,
            'model_type' => User::class,
        ]);

        // 3. Create a rule that targets 'approver' role by ID
        $rule = new ApprovalRule;
        $rule->approver_type = 'Role';
        $rule->approver_role = [$roleId];

        // 4. Verify it returns only the intended user
        $users = $this->service->getEligibleUsers($rule);

        $this->assertCount(1, $users);
        $this->assertEquals($user->id, $users->first()->id);
    }
}
