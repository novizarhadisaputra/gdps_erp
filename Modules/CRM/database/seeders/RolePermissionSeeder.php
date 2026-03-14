<?php

namespace Modules\CRM\Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->assignDireksiPermissions();
        $this->assignVPBusinessSupportPermissions();
        $this->assignVPFinancePermissions();
        $this->assignVPOpsPermissions();
        $this->assignVPHumanCapitalPermissions();
    }

    protected function assignDireksiPermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'Direksi', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:Lead', 'View:Lead',
            'ViewAny:Proposal', 'View:Proposal',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement',
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis',
            'ViewAny:Project', 'View:Project',
            'ViewAny:Customer', 'View:Customer',
            'ViewAny:Contract', 'View:Contract',
            'View:CRMCluster', 'View:FinanceCluster', 'View:ProjectCluster', 'View:MasterDataCluster',
            'View:CRMAnalyticsPage', 'View:ProjectAnalyticsPage', 'View:ProjectDashboard',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function assignVPBusinessSupportPermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'VP Business Support', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:Lead', 'View:Lead', 'Create:Lead', 'Update:Lead',
            'ViewAny:Proposal', 'View:Proposal', 'Create:Proposal', 'Update:Proposal',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement', 'Create:MinutesOfAgreement', 'Update:MinutesOfAgreement',
            'ViewAny:Customer', 'View:Customer', 'Create:Customer', 'Update:Customer',
            'ViewAny:GeneralInformation', 'View:GeneralInformation', 'Create:GeneralInformation', 'Update:GeneralInformation',
            'ViewAny:SalesPlan', 'View:SalesPlan',
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis',
            'View:CRMCluster', 'View:CRMAnalyticsPage',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function assignVPFinancePermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'VP Finance', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis', 'Create:ProfitabilityAnalysis', 'Update:ProfitabilityAnalysis',
            'ViewAny:SalesPlan', 'View:SalesPlan', 'Create:SalesPlan', 'Update:SalesPlan',
            'ViewAny:ProfitabilityThreshold', 'View:ProfitabilityThreshold', 'Update:ProfitabilityThreshold',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement',
            'ViewAny:Tax', 'View:Tax',
            'ViewAny:PaymentTerm', 'View:PaymentTerm',
            'ViewAny:BankAccount', 'View:BankAccount',
            'View:FinanceCluster', 'View:CRMCluster',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function assignVPOpsPermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'VP Ops', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:Project', 'View:Project', 'Update:Project',
            'ViewAny:Contract', 'View:Contract',
            'ViewAny:Lead', 'View:Lead',
            'View:ProjectCluster', 'View:CRMCluster', 'View:ProjectDashboard',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function assignVPHumanCapitalPermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'VP Human Capital', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:Employee', 'View:Employee',
            'ViewAny:ManpowerTemplate', 'View:ManpowerTemplate',
            'ViewAny:JobPosition', 'View:JobPosition',
            'View:CRMCluster', 'View:MasterDataCluster',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function syncPermissions(Role $role, array $permissionNames): void
    {
        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role->syncPermissions($permissionNames);
    }
}
