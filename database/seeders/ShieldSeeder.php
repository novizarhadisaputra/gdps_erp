<?php

namespace Database\Seeders;

use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $tenants = '[]';
        $users = '[]';
        $userTenantPivot = '[]';
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:Activity","View:Activity","Create:Activity","Update:Activity","Delete:Activity","Restore:Activity","ForceDelete:Activity","ForceDeleteAny:Activity","RestoreAny:Activity","Replicate:Activity","Reorder:Activity","ViewAny:FileSystemItem","View:FileSystemItem","Create:FileSystemItem","Update:FileSystemItem","Delete:FileSystemItem","Restore:FileSystemItem","ForceDelete:FileSystemItem","ForceDeleteAny:FileSystemItem","RestoreAny:FileSystemItem","Replicate:FileSystemItem","Reorder:FileSystemItem","ViewAny:Contract","View:Contract","Create:Contract","Update:Contract","Delete:Contract","Restore:Contract","ForceDelete:Contract","ForceDeleteAny:Contract","RestoreAny:Contract","Replicate:Contract","Reorder:Contract","ViewAny:Proposal","View:Proposal","Create:Proposal","Update:Proposal","Delete:Proposal","Restore:Proposal","ForceDelete:Proposal","ForceDeleteAny:Proposal","RestoreAny:Proposal","Replicate:Proposal","Reorder:Proposal","ViewAny:ProfitabilityAnalysis","View:ProfitabilityAnalysis","Create:ProfitabilityAnalysis","Update:ProfitabilityAnalysis","Delete:ProfitabilityAnalysis","Restore:ProfitabilityAnalysis","ForceDelete:ProfitabilityAnalysis","ForceDeleteAny:ProfitabilityAnalysis","RestoreAny:ProfitabilityAnalysis","Replicate:ProfitabilityAnalysis","Reorder:ProfitabilityAnalysis","ViewAny:Unit","View:Unit","Create:Unit","Update:Unit","Delete:Unit","Restore:Unit","ForceDelete:Unit","ForceDeleteAny:Unit","RestoreAny:Unit","Replicate:Unit","Reorder:Unit","ViewAny:BillingOption","View:BillingOption","Create:BillingOption","Update:BillingOption","Delete:BillingOption","Restore:BillingOption","ForceDelete:BillingOption","ForceDeleteAny:BillingOption","RestoreAny:BillingOption","Replicate:BillingOption","Reorder:BillingOption","ViewAny:Client","View:Client","Create:Client","Update:Client","Delete:Client","Restore:Client","ForceDelete:Client","ForceDeleteAny:Client","RestoreAny:Client","Replicate:Client","Reorder:Client","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee","ViewAny:ItemCategory","View:ItemCategory","Create:ItemCategory","Update:ItemCategory","Delete:ItemCategory","Restore:ItemCategory","ForceDelete:ItemCategory","ForceDeleteAny:ItemCategory","RestoreAny:ItemCategory","Replicate:ItemCategory","Reorder:ItemCategory","ViewAny:Item","View:Item","Create:Item","Update:Item","Delete:Item","Restore:Item","ForceDelete:Item","ForceDeleteAny:Item","RestoreAny:Item","Replicate:Item","Reorder:Item","ViewAny:PaymentTerm","View:PaymentTerm","Create:PaymentTerm","Update:PaymentTerm","Delete:PaymentTerm","Restore:PaymentTerm","ForceDelete:PaymentTerm","ForceDeleteAny:PaymentTerm","RestoreAny:PaymentTerm","Replicate:PaymentTerm","Reorder:PaymentTerm","ViewAny:ProductCluster","View:ProductCluster","Create:ProductCluster","Update:ProductCluster","Delete:ProductCluster","Restore:ProductCluster","ForceDelete:ProductCluster","ForceDeleteAny:ProductCluster","RestoreAny:ProductCluster","Replicate:ProductCluster","Reorder:ProductCluster","ViewAny:ProjectArea","View:ProjectArea","Create:ProjectArea","Update:ProjectArea","Delete:ProjectArea","Restore:ProjectArea","ForceDelete:ProjectArea","ForceDeleteAny:ProjectArea","RestoreAny:ProjectArea","Replicate:ProjectArea","Reorder:ProjectArea","ViewAny:ProjectType","View:ProjectType","Create:ProjectType","Update:ProjectType","Delete:ProjectType","Restore:ProjectType","ForceDelete:ProjectType","ForceDeleteAny:ProjectType","RestoreAny:ProjectType","Replicate:ProjectType","Reorder:ProjectType","ViewAny:Tax","View:Tax","Create:Tax","Update:Tax","Delete:Tax","Restore:Tax","ForceDelete:Tax","ForceDeleteAny:Tax","RestoreAny:Tax","Replicate:Tax","Reorder:Tax","ViewAny:UnitOfMeasure","View:UnitOfMeasure","Create:UnitOfMeasure","Update:UnitOfMeasure","Delete:UnitOfMeasure","Restore:UnitOfMeasure","ForceDelete:UnitOfMeasure","ForceDeleteAny:UnitOfMeasure","RestoreAny:UnitOfMeasure","Replicate:UnitOfMeasure","Reorder:UnitOfMeasure","ViewAny:WorkScheme","View:WorkScheme","Create:WorkScheme","Update:WorkScheme","Delete:WorkScheme","Restore:WorkScheme","ForceDelete:WorkScheme","ForceDeleteAny:WorkScheme","RestoreAny:WorkScheme","Replicate:WorkScheme","Reorder:WorkScheme","ViewAny:ProjectInformation","View:ProjectInformation","Create:ProjectInformation","Update:ProjectInformation","Delete:ProjectInformation","Restore:ProjectInformation","ForceDelete:ProjectInformation","ForceDeleteAny:ProjectInformation","RestoreAny:ProjectInformation","Replicate:ProjectInformation","Reorder:ProjectInformation","ViewAny:Project","View:Project","Create:Project","Update:Project","Delete:Project","Restore:Project","ForceDelete:Project","ForceDeleteAny:Project","RestoreAny:Project","Replicate:Project","Reorder:Project","View:CRMCluster","View:FinanceCluster","View:MasterDataCluster","View:ProjectCluster","View:FileManager","View:FileSystem","View:SchemaExample","View:EmbedConfigTest"]},{"name":"panel_user","guard_name":"web","permissions":[]}]';
        $directPermissions = '[]';

        // 1. Seed tenants first (if present)
        if (! blank($tenants) && $tenants !== '[]') {
            static::seedTenants($tenants);
        }

        // 2. Seed roles with permissions
        static::makeRolesWithPermissions($rolesWithPermissions);

        // 3. Seed direct permissions
        static::makeDirectPermissions($directPermissions);

        // 4. Seed users with their roles/permissions (if present)
        if (! blank($users) && $users !== '[]') {
            static::seedUsers($users);
        }

        // 5. Seed user-tenant pivot (if present)
        if (! blank($userTenantPivot) && $userTenantPivot !== '[]') {
            static::seedUserTenantPivot($userTenantPivot);
        }

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function seedTenants(string $tenants): void
    {
        if (blank($tenantData = json_decode($tenants, true))) {
            return;
        }

        $tenantModel = '';
        if (blank($tenantModel)) {
            return;
        }

        foreach ($tenantData as $tenant) {
            $tenantModel::firstOrCreate(
                ['id' => $tenant['id']],
                $tenant
            );
        }
    }

    protected static function seedUsers(string $users): void
    {
        if (blank($userData = json_decode($users, true))) {
            return;
        }

        $userModel = 'App\Models\User';
        $tenancyEnabled = false;

        foreach ($userData as $data) {
            // Extract role/permission data before creating user
            $roles = $data['roles'] ?? [];
            $permissions = $data['permissions'] ?? [];
            $tenantRoles = $data['tenant_roles'] ?? [];
            $tenantPermissions = $data['tenant_permissions'] ?? [];
            unset($data['roles'], $data['permissions'], $data['tenant_roles'], $data['tenant_permissions']);

            $user = $userModel::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            // Handle tenancy mode - sync roles/permissions per tenant
            if ($tenancyEnabled && (! empty($tenantRoles) || ! empty($tenantPermissions))) {
                foreach ($tenantRoles as $tenantId => $roleNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncRoles($roleNames);
                }

                foreach ($tenantPermissions as $tenantId => $permissionNames) {
                    $contextId = $tenantId === '_global' ? null : $tenantId;
                    setPermissionsTeamId($contextId);
                    $user->syncPermissions($permissionNames);
                }
            } else {
                // Non-tenancy mode
                if (! empty($roles)) {
                    $user->syncRoles($roles);
                }

                if (! empty($permissions)) {
                    $user->syncPermissions($permissions);
                }
            }
        }
    }

    protected static function seedUserTenantPivot(string $pivot): void
    {
        if (blank($pivotData = json_decode($pivot, true))) {
            return;
        }

        $pivotTable = '';
        if (blank($pivotTable)) {
            return;
        }

        foreach ($pivotData as $row) {
            $uniqueKeys = [];

            if (isset($row['user_id'])) {
                $uniqueKeys['user_id'] = $row['user_id'];
            }

            $tenantForeignKey = 'team_id';
            if (! blank($tenantForeignKey) && isset($row[$tenantForeignKey])) {
                $uniqueKeys[$tenantForeignKey] = $row[$tenantForeignKey];
            }

            if (! empty($uniqueKeys)) {
                DB::table($pivotTable)->updateOrInsert($uniqueKeys, $row);
            }
        }
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $roleModel */
        $roleModel = Utils::getRoleModel();
        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        $tenancyEnabled = false;
        $teamForeignKey = 'team_id';

        foreach ($rolePlusPermissions as $rolePlusPermission) {
            $tenantId = $rolePlusPermission[$teamForeignKey] ?? null;

            // Set tenant context for role creation and permission sync
            if ($tenancyEnabled) {
                setPermissionsTeamId($tenantId);
            }

            $roleData = [
                'name' => $rolePlusPermission['name'],
                'guard_name' => $rolePlusPermission['guard_name'],
            ];

            // Include tenant ID in role data (can be null for global roles)
            if ($tenancyEnabled && ! blank($teamForeignKey)) {
                $roleData[$teamForeignKey] = $tenantId;
            }

            $role = $roleModel::firstOrCreate($roleData);

            if (! blank($rolePlusPermission['permissions'])) {
                $permissionModels = collect($rolePlusPermission['permissions'])
                    ->map(fn ($permission) => $permissionModel::firstOrCreate([
                        'name' => $permission,
                        'guard_name' => $rolePlusPermission['guard_name'],
                    ]))
                    ->all();

                $role->syncPermissions($permissionModels);
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (blank($permissions = json_decode($directPermissions, true))) {
            return;
        }

        /** @var \Illuminate\Database\Eloquent\Model $permissionModel */
        $permissionModel = Utils::getPermissionModel();

        foreach ($permissions as $permission) {
            if ($permissionModel::whereName($permission['name'])->doesntExist()) {
                $permissionModel::create([
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]);
            }
        }
    }
}
