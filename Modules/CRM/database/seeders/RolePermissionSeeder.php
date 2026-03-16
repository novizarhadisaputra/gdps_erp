<?php

namespace Modules\CRM\Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
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
            'ViewAny:SalesOrder', 'View:SalesOrder',
            'ViewAny:Invoice', 'View:Invoice',
            'ViewAny:WorkCompletionReport', 'View:WorkCompletionReport',
            'ViewAny:ProjectReview', 'View:ProjectReview',
            'View:CRMCluster', 'View:FinanceCluster', 'View:ProjectCluster', 'View:MasterDataCluster',
            'View:CRMAnalyticsPage', 'View:ProjectAnalyticsPage', 'View:ProjectDashboard', 'View:ProjectReviewDashboard',
        ];

        $this->syncPermissions($role, $permissions);
    }

    protected function assignVPBusinessSupportPermissions(): void
    {
        $role = Role::firstOrCreate(['name' => 'VP Business Support', 'guard_name' => 'web']);

        $permissions = [
            'ViewAny:Lead', 'View:Lead', 'Create:Lead', 'Update:Lead',
            'ViewAny:Proposal', 'View:Proposal', 'Create:Proposal', 'Update:Proposal', 'SendEmail:Proposal',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement', 'Create:MinutesOfAgreement', 'Update:MinutesOfAgreement',
            'ViewAny:Customer', 'View:Customer', 'Create:Customer', 'Update:Customer',
            'ViewAny:GeneralInformation', 'View:GeneralInformation', 'Create:GeneralInformation', 'Update:GeneralInformation',
            'ViewAny:SalesPlan', 'View:SalesPlan',
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis',
            'ViewAny:SalesOrder', 'View:SalesOrder', 'Create:SalesOrder', 'Update:SalesOrder', 'SendEmail:SalesOrder',
            'ViewAny:Invoice', 'View:Invoice',
            'ViewAny:WorkCompletionReport', 'View:WorkCompletionReport',
            'View:CRMCluster', 'View:CRMAnalyticsPage', 'View:ProjectReviewDashboard',
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
            'ViewAny:Invoice', 'View:Invoice', 'Create:Invoice', 'Update:Invoice', 'SendEmail:Invoice',
            'ViewAny:SalesOrder', 'View:SalesOrder', 'Update:SalesOrder',
            'View:FinanceCluster', 'View:CRMCluster', 'View:ProjectReviewDashboard',
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
            'ViewAny:WorkCompletionReport', 'View:WorkCompletionReport', 'Create:WorkCompletionReport', 'Update:WorkCompletionReport', 'SendEmail:WorkCompletionReport',
            'ViewAny:SalesOrder', 'View:SalesOrder',
            'ViewAny:Invoice', 'View:Invoice',
            'View:ProjectCluster', 'View:CRMCluster', 'View:ProjectDashboard', 'View:ProjectReviewDashboard',
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
