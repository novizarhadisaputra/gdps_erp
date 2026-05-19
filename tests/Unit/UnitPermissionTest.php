<?php

namespace Tests\Unit;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\Unit;
use Tests\TestCase;

class UnitPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_inherits_permissions_from_assigned_unit(): void
    {
        // 1. Create a unit
        /** @var Unit $unit */
        $unit = Unit::factory()->create([
            'name' => 'Finance Unit',
            'code' => 'UF',
            'external_id' => 'finance-123',
        ]);

        // 2. Create a permission
        Permission::firstOrCreate(['name' => 'View:CRMCluster', 'guard_name' => 'web']);

        // 3. Assign permission to the unit
        $unit->givePermissionTo('View:CRMCluster');

        // 4. Create a user assigned to this unit
        /** @var User $user */
        $user = User::factory()->create([
            'unit_id' => 'finance-123',
            'unit' => 'Finance Unit',
        ]);

        // 5. Verify the user has the permission via inheritance
        $this->assertTrue($user->hasPermissionTo('View:CRMCluster'));
        $this->assertTrue($user->can('View:CRMCluster'));
    }

    public function test_user_does_not_have_permission_if_unit_does_not_have_it(): void
    {
        // 1. Create a unit
        /** @var Unit $unit */
        $unit = Unit::factory()->create([
            'name' => 'Operations Unit',
            'code' => 'UO',
            'external_id' => 'operations-456',
        ]);

        // 2. Create a permission
        Permission::firstOrCreate(['name' => 'View:CRMCluster', 'guard_name' => 'web']);

        // 3. Create a user assigned to this unit
        /** @var User $user */
        $user = User::factory()->create([
            'unit_id' => 'operations-456',
            'unit' => 'Operations Unit',
        ]);

        // 4. Verify the user does not have the permission
        $this->assertFalse($user->hasPermissionTo('View:CRMCluster'));
        $this->assertFalse($user->can('View:CRMCluster'));
    }
}
