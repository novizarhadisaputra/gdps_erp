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
        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["ViewAny:Role","View:Role","Create:Role","Update:Role","Delete:Role","Restore:Role","ForceDelete:Role","ForceDeleteAny:Role","RestoreAny:Role","Replicate:Role","Reorder:Role","ViewAny:Activity","View:Activity","Create:Activity","Update:Activity","Delete:Activity","Restore:Activity","ForceDelete:Activity","ForceDeleteAny:Activity","RestoreAny:Activity","Replicate:Activity","Reorder:Activity","ViewAny:FileSystemItem","View:FileSystemItem","Create:FileSystemItem","Update:FileSystemItem","Delete:FileSystemItem","Restore:FileSystemItem","ForceDelete:FileSystemItem","ForceDeleteAny:FileSystemItem","RestoreAny:FileSystemItem","Replicate:FileSystemItem","Reorder:FileSystemItem","ViewAny:Contract","View:Contract","Create:Contract","Update:Contract","Delete:Contract","Restore:Contract","ForceDelete:Contract","ForceDeleteAny:Contract","RestoreAny:Contract","Replicate:Contract","Reorder:Contract","ViewAny:Proposal","View:Proposal","Create:Proposal","Update:Proposal","Delete:Proposal","Restore:Proposal","ForceDelete:Proposal","ForceDeleteAny:Proposal","RestoreAny:Proposal","Replicate:Proposal","Reorder:Proposal","ViewAny:ProfitabilityAnalysis","View:ProfitabilityAnalysis","Create:ProfitabilityAnalysis","Update:ProfitabilityAnalysis","Delete:ProfitabilityAnalysis","Restore:ProfitabilityAnalysis","ForceDelete:ProfitabilityAnalysis","ForceDeleteAny:ProfitabilityAnalysis","RestoreAny:ProfitabilityAnalysis","Replicate:ProfitabilityAnalysis","Reorder:ProfitabilityAnalysis","ViewAny:Unit","View:Unit","Create:Unit","Update:Unit","Delete:Unit","Restore:Unit","ForceDelete:Unit","ForceDeleteAny:Unit","RestoreAny:Unit","Replicate:Unit","Reorder:Unit","ViewAny:BillingOption","View:BillingOption","Create:BillingOption","Update:BillingOption","Delete:BillingOption","Restore:BillingOption","ForceDelete:BillingOption","ForceDeleteAny:BillingOption","RestoreAny:BillingOption","Replicate:BillingOption","Reorder:BillingOption","ViewAny:Customer","View:Customer","Create:Customer","Update:Customer","Delete:Customer","Restore:Customer","ForceDelete:Customer","ForceDeleteAny:Customer","RestoreAny:Customer","Replicate:Customer","Reorder:Customer","ViewAny:Employee","View:Employee","Create:Employee","Update:Employee","Delete:Employee","Restore:Employee","ForceDelete:Employee","ForceDeleteAny:Employee","RestoreAny:Employee","Replicate:Employee","Reorder:Employee","ViewAny:ItemCategory","View:ItemCategory","Create:ItemCategory","Update:ItemCategory","Delete:ItemCategory","Restore:ItemCategory","ForceDelete:ItemCategory","ForceDeleteAny:ItemCategory","RestoreAny:ItemCategory","Replicate:ItemCategory","Reorder:ItemCategory","ViewAny:Item","View:Item","Create:Item","Update:Item","Delete:Item","Restore:Item","ForceDelete:Item","ForceDeleteAny:Item","RestoreAny:Item","Replicate:Item","Reorder:Item","ViewAny:PaymentTerm","View:PaymentTerm","Create:PaymentTerm","Update:PaymentTerm","Delete:PaymentTerm","Restore:PaymentTerm","ForceDelete:PaymentTerm","ForceDeleteAny:PaymentTerm","RestoreAny:PaymentTerm","Replicate:PaymentTerm","Reorder:PaymentTerm","ViewAny:ProductCluster","View:ProductCluster","Create:ProductCluster","Update:ProductCluster","Delete:ProductCluster","Restore:ProductCluster","ForceDelete:ProductCluster","ForceDeleteAny:ProductCluster","RestoreAny:ProductCluster","Replicate:ProductCluster","Reorder:ProductCluster","ViewAny:ProjectArea","View:ProjectArea","Create:ProjectArea","Update:ProjectArea","Delete:ProjectArea","Restore:ProjectArea","ForceDelete:ProjectArea","ForceDeleteAny:ProjectArea","RestoreAny:ProjectArea","Replicate:ProjectArea","Reorder:ProjectArea","ViewAny:ProjectType","View:ProjectType","Create:ProjectType","Update:ProjectType","Delete:ProjectType","Restore:ProjectType","ForceDelete:ProjectType","ForceDeleteAny:ProjectType","RestoreAny:ProjectType","Replicate:ProjectType","Reorder:ProjectType","ViewAny:Tax","View:Tax","Create:Tax","Update:Tax","Delete:Tax","Restore:Tax","ForceDelete:Tax","ForceDeleteAny:Tax","RestoreAny:Tax","Replicate:Tax","Reorder:Tax","ViewAny:UnitOfMeasure","View:UnitOfMeasure","Create:UnitOfMeasure","Update:UnitOfMeasure","Delete:UnitOfMeasure","Restore:UnitOfMeasure","ForceDelete:UnitOfMeasure","ForceDeleteAny:UnitOfMeasure","RestoreAny:UnitOfMeasure","Replicate:UnitOfMeasure","Reorder:UnitOfMeasure","ViewAny:WorkScheme","View:WorkScheme","Create:WorkScheme","Update:WorkScheme","Delete:WorkScheme","Restore:WorkScheme","ForceDelete:WorkScheme","ForceDeleteAny:WorkScheme","RestoreAny:WorkScheme","Replicate:WorkScheme","Reorder:WorkScheme","ViewAny:ProjectInformation","View:ProjectInformation","Create:ProjectInformation","Update:ProjectInformation","Delete:ProjectInformation","Restore:ProjectInformation","ForceDelete:ProjectInformation","ForceDeleteAny:ProjectInformation","RestoreAny:ProjectInformation","Replicate:ProjectInformation","Reorder:ProjectInformation","ViewAny:Project","View:Project","Create:Project","Update:Project","Delete:Project","Restore:Project","ForceDelete:Project","ForceDeleteAny:Project","RestoreAny:Project","Replicate:Project","Reorder:Project","View:CRMCluster","View:FinanceCluster","View:MasterDataCluster","View:ProjectCluster","View:FileManager","View:FileSystem","View:SchemaExample","View:EmbedConfigTest","ViewAny:Lead","View:Lead","Create:Lead","Update:Lead","Delete:Lead","Restore:Lead","ForceDelete:Lead","ForceDeleteAny:Lead","RestoreAny:Lead","Replicate:Lead","Reorder:Lead","ViewAny:CostingTemplate","View:CostingTemplate","Create:CostingTemplate","Update:CostingTemplate","Delete:CostingTemplate","Restore:CostingTemplate","ForceDelete:CostingTemplate","ForceDeleteAny:CostingTemplate","RestoreAny:CostingTemplate","Replicate:CostingTemplate","Reorder:CostingTemplate","ViewAny:CostingTemplateItem","View:CostingTemplateItem","Create:CostingTemplateItem","Update:CostingTemplateItem","Delete:CostingTemplateItem","Restore:CostingTemplateItem","ForceDelete:CostingTemplateItem","ForceDeleteAny:CostingTemplateItem","RestoreAny:CostingTemplateItem","Replicate:CostingTemplateItem","Reorder:CostingTemplateItem","ViewAny:GeneralInformation","View:GeneralInformation","Create:GeneralInformation","Update:GeneralInformation","Delete:GeneralInformation","Restore:GeneralInformation","ForceDelete:GeneralInformation","ForceDeleteAny:GeneralInformation","RestoreAny:GeneralInformation","Replicate:GeneralInformation","Reorder:GeneralInformation","ViewAny:ManpowerTemplate","View:ManpowerTemplate","Create:ManpowerTemplate","Update:ManpowerTemplate","Delete:ManpowerTemplate","Restore:ManpowerTemplate","ForceDelete:ManpowerTemplate","ForceDeleteAny:ManpowerTemplate","RestoreAny:ManpowerTemplate","Replicate:ManpowerTemplate","Reorder:ManpowerTemplate","ViewAny:MinutesOfAgreement","View:MinutesOfAgreement","Create:MinutesOfAgreement","Update:MinutesOfAgreement","Delete:MinutesOfAgreement","Restore:MinutesOfAgreement","ForceDelete:MinutesOfAgreement","ForceDeleteAny:MinutesOfAgreement","RestoreAny:MinutesOfAgreement","Replicate:MinutesOfAgreement","Reorder:MinutesOfAgreement","ViewAny:SalesPlan","View:SalesPlan","Create:SalesPlan","Update:SalesPlan","Delete:SalesPlan","Restore:SalesPlan","ForceDelete:SalesPlan","ForceDeleteAny:SalesPlan","RestoreAny:SalesPlan","Replicate:SalesPlan","Reorder:SalesPlan","ViewAny:ProfitabilityThreshold","View:ProfitabilityThreshold","Create:ProfitabilityThreshold","Update:ProfitabilityThreshold","Delete:ProfitabilityThreshold","Restore:ProfitabilityThreshold","ForceDelete:ProfitabilityThreshold","ForceDeleteAny:ProfitabilityThreshold","RestoreAny:ProfitabilityThreshold","Replicate:ProfitabilityThreshold","Reorder:ProfitabilityThreshold","ViewAny:ApiClient","View:ApiClient","Create:ApiClient","Update:ApiClient","Delete:ApiClient","Restore:ApiClient","ForceDelete:ApiClient","ForceDeleteAny:ApiClient","RestoreAny:ApiClient","Replicate:ApiClient","Reorder:ApiClient","ViewAny:ApprovalRule","View:ApprovalRule","Create:ApprovalRule","Update:ApprovalRule","Delete:ApprovalRule","Restore:ApprovalRule","ForceDelete:ApprovalRule","ForceDeleteAny:ApprovalRule","RestoreAny:ApprovalRule","Replicate:ApprovalRule","Reorder:ApprovalRule","ViewAny:AssetGroup","View:AssetGroup","Create:AssetGroup","Update:AssetGroup","Delete:AssetGroup","Restore:AssetGroup","ForceDelete:AssetGroup","ForceDeleteAny:AssetGroup","RestoreAny:AssetGroup","Replicate:AssetGroup","Reorder:AssetGroup","ViewAny:BankAccount","View:BankAccount","Create:BankAccount","Update:BankAccount","Delete:BankAccount","Restore:BankAccount","ForceDelete:BankAccount","ForceDeleteAny:BankAccount","RestoreAny:BankAccount","Replicate:BankAccount","Reorder:BankAccount","ViewAny:BenefitType","View:BenefitType","Create:BenefitType","Update:BenefitType","Delete:BenefitType","Restore:BenefitType","ForceDelete:BenefitType","ForceDeleteAny:BenefitType","RestoreAny:BenefitType","Replicate:BenefitType","Reorder:BenefitType","ViewAny:BpjsBasisType","View:BpjsBasisType","Create:BpjsBasisType","Update:BpjsBasisType","Delete:BpjsBasisType","Restore:BpjsBasisType","ForceDelete:BpjsBasisType","ForceDeleteAny:BpjsBasisType","RestoreAny:BpjsBasisType","Replicate:BpjsBasisType","Reorder:BpjsBasisType","ViewAny:BufferCostType","View:BufferCostType","Create:BufferCostType","Update:BufferCostType","Delete:BufferCostType","Restore:BufferCostType","ForceDelete:BufferCostType","ForceDeleteAny:BufferCostType","RestoreAny:BufferCostType","Replicate:BufferCostType","Reorder:BufferCostType","ViewAny:ContactRole","View:ContactRole","Create:ContactRole","Update:ContactRole","Delete:ContactRole","Restore:ContactRole","ForceDelete:ContactRole","ForceDeleteAny:ContactRole","RestoreAny:ContactRole","Replicate:ContactRole","Reorder:ContactRole","ViewAny:ContractType","View:ContractType","Create:ContractType","Update:ContractType","Delete:ContractType","Restore:ContractType","ForceDelete:ContractType","ForceDeleteAny:ContractType","RestoreAny:ContractType","Replicate:ContractType","Reorder:ContractType","ViewAny:DirectCostCategory","View:DirectCostCategory","Create:DirectCostCategory","Update:DirectCostCategory","Delete:DirectCostCategory","Restore:DirectCostCategory","ForceDelete:DirectCostCategory","ForceDeleteAny:DirectCostCategory","RestoreAny:DirectCostCategory","Replicate:DirectCostCategory","Reorder:DirectCostCategory","ViewAny:FixedAllowance","View:FixedAllowance","Create:FixedAllowance","Update:FixedAllowance","Delete:FixedAllowance","Restore:FixedAllowance","ForceDelete:FixedAllowance","ForceDeleteAny:FixedAllowance","RestoreAny:FixedAllowance","Replicate:FixedAllowance","Reorder:FixedAllowance","ViewAny:HealthConfig","View:HealthConfig","Create:HealthConfig","Update:HealthConfig","Delete:HealthConfig","Restore:HealthConfig","ForceDelete:HealthConfig","ForceDeleteAny:HealthConfig","RestoreAny:HealthConfig","Replicate:HealthConfig","Reorder:HealthConfig","ViewAny:IndustrialSector","View:IndustrialSector","Create:IndustrialSector","Update:IndustrialSector","Delete:IndustrialSector","Restore:IndustrialSector","ForceDelete:IndustrialSector","ForceDeleteAny:IndustrialSector","RestoreAny:IndustrialSector","Replicate:IndustrialSector","Reorder:IndustrialSector","ViewAny:JhtConfig","View:JhtConfig","Create:JhtConfig","Update:JhtConfig","Delete:JhtConfig","Restore:JhtConfig","ForceDelete:JhtConfig","ForceDeleteAny:JhtConfig","RestoreAny:JhtConfig","Replicate:JhtConfig","Reorder:JhtConfig","ViewAny:JkkConfig","View:JkkConfig","Create:JkkConfig","Update:JkkConfig","Delete:JkkConfig","Restore:JkkConfig","ForceDelete:JkkConfig","ForceDeleteAny:JkkConfig","RestoreAny:JkkConfig","Replicate:JkkConfig","Reorder:JkkConfig","ViewAny:JkmConfig","View:JkmConfig","Create:JkmConfig","Update:JkmConfig","Delete:JkmConfig","Restore:JkmConfig","ForceDelete:JkmConfig","ForceDeleteAny:JkmConfig","RestoreAny:JkmConfig","Replicate:JkmConfig","Reorder:JkmConfig","ViewAny:JobPosition","View:JobPosition","Create:JobPosition","Update:JobPosition","Delete:JobPosition","Restore:JobPosition","ForceDelete:JobPosition","ForceDeleteAny:JobPosition","RestoreAny:JobPosition","Replicate:JobPosition","Reorder:JobPosition","ViewAny:JpConfig","View:JpConfig","Create:JpConfig","Update:JpConfig","Delete:JpConfig","Restore:JpConfig","ForceDelete:JpConfig","ForceDeleteAny:JpConfig","RestoreAny:JpConfig","Replicate:JpConfig","Reorder:JpConfig","ViewAny:NonFixedAllowance","View:NonFixedAllowance","Create:NonFixedAllowance","Update:NonFixedAllowance","Delete:NonFixedAllowance","Restore:NonFixedAllowance","ForceDelete:NonFixedAllowance","ForceDeleteAny:NonFixedAllowance","RestoreAny:NonFixedAllowance","Replicate:NonFixedAllowance","Reorder:NonFixedAllowance","ViewAny:PartnerFeeType","View:PartnerFeeType","Create:PartnerFeeType","Update:PartnerFeeType","Delete:PartnerFeeType","Restore:PartnerFeeType","ForceDelete:PartnerFeeType","ForceDeleteAny:PartnerFeeType","RestoreAny:PartnerFeeType","Replicate:PartnerFeeType","Reorder:PartnerFeeType","ViewAny:PtkpConfig","View:PtkpConfig","Create:PtkpConfig","Update:PtkpConfig","Delete:PtkpConfig","Restore:PtkpConfig","ForceDelete:PtkpConfig","ForceDeleteAny:PtkpConfig","RestoreAny:PtkpConfig","Replicate:PtkpConfig","Reorder:PtkpConfig","ViewAny:RegencyMinimumWage","View:RegencyMinimumWage","Create:RegencyMinimumWage","Update:RegencyMinimumWage","Delete:RegencyMinimumWage","Restore:RegencyMinimumWage","ForceDelete:RegencyMinimumWage","ForceDeleteAny:RegencyMinimumWage","RestoreAny:RegencyMinimumWage","Replicate:RegencyMinimumWage","Reorder:RegencyMinimumWage","ViewAny:RevenueSegment","View:RevenueSegment","Create:RevenueSegment","Update:RevenueSegment","Delete:RevenueSegment","Restore:RevenueSegment","ForceDelete:RevenueSegment","ForceDeleteAny:RevenueSegment","RestoreAny:RevenueSegment","Replicate:RevenueSegment","Reorder:RevenueSegment","ViewAny:SkillCategory","View:SkillCategory","Create:SkillCategory","Update:SkillCategory","Delete:SkillCategory","Restore:SkillCategory","ForceDelete:SkillCategory","ForceDeleteAny:SkillCategory","RestoreAny:SkillCategory","Replicate:SkillCategory","Reorder:SkillCategory","ViewAny:TaxScheme","View:TaxScheme","Create:TaxScheme","Update:TaxScheme","Delete:TaxScheme","Restore:TaxScheme","ForceDelete:TaxScheme","ForceDeleteAny:TaxScheme","RestoreAny:TaxScheme","Replicate:TaxScheme","Reorder:TaxScheme","ViewAny:ThrBasisType","View:ThrBasisType","Create:ThrBasisType","Update:ThrBasisType","Delete:ThrBasisType","Restore:ThrBasisType","ForceDelete:ThrBasisType","ForceDeleteAny:ThrBasisType","RestoreAny:ThrBasisType","Replicate:ThrBasisType","Reorder:ThrBasisType","ViewAny:Vendor","View:Vendor","Create:Vendor","Update:Vendor","Delete:Vendor","Restore:Vendor","ForceDelete:Vendor","ForceDeleteAny:Vendor","RestoreAny:Vendor","Replicate:Vendor","Reorder:Vendor","ViewAny:SalesOrder","View:SalesOrder","Create:SalesOrder","Update:SalesOrder","Delete:SalesOrder","Restore:SalesOrder","ForceDelete:SalesOrder","ForceDeleteAny:SalesOrder","RestoreAny:SalesOrder","Replicate:SalesOrder","Reorder:SalesOrder","ViewAny:Invoice","View:Invoice","Create:Invoice","Update:Invoice","Delete:Invoice","Restore:Invoice","ForceDelete:Invoice","ForceDeleteAny:Invoice","RestoreAny:Invoice","Replicate:Invoice","Reorder:Invoice","ViewAny:WorkCompletionReport","View:WorkCompletionReport","Create:WorkCompletionReport","Update:WorkCompletionReport","Delete:WorkCompletionReport","Restore:WorkCompletionReport","ForceDelete:WorkCompletionReport","ForceDeleteAny:WorkCompletionReport","RestoreAny:WorkCompletionReport","Replicate:WorkCompletionReport","Reorder:WorkCompletionReport","ViewAny:ProjectReview","View:ProjectReview","Create:ProjectReview","Update:ProjectReview","Delete:ProjectReview","Restore:ProjectReview","ForceDelete:ProjectReview","ForceDeleteAny:ProjectReview","RestoreAny:ProjectReview","Replicate:ProjectReview","Reorder:ProjectReview","SendEmail:Proposal","SendEmail:SalesOrder","SendEmail:WorkCompletionReport","SendEmail:Invoice","View:ProjectReviewDashboard","View:CRMAnalyticsPage","View:ProjectAnalyticsPage","View:ProjectDashboard","View:ViewLog"]},{"name":"panel_user","guard_name":"web","permissions":[]}]';
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
