<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Modules\MasterData\Models\Unit;

class UnitPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding permissions for existing units...');

        // 1. Fetch permissions from RolePermissionSeeder to ensure exact alignment
        $bodPermissions = [
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
            'ViewAny:ProposalRevision', 'View:ProposalRevision',
            'ViewAny:ProfitabilityAnalysisRevision', 'View:ProfitabilityAnalysisRevision',
            'ViewAny:SalesOrderAmendment', 'View:SalesOrderAmendment',
            'ViewAny:Comment', 'View:Comment',
            'View:CRMCluster', 'View:FinanceCluster', 'View:ProjectCluster', 'View:MasterDataCluster',
            'View:CRMAnalyticsPage', 'View:ProjectAnalyticsPage', 'View:ProjectDashboard', 'View:ProjectReviewDashboard',
            'View:ProjectBoard', 'View:SummaryProfitabilityAnalysis',
            'View:AuditProposalRevision', 'View:AuditProfitabilityAnalysisRevision', 'View:AuditAmendment',
        ];

        $businessSupportPermissions = [
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
            'ViewAny:ProjectReview', 'View:ProjectReview',
            'ViewAny:ProposalRevision', 'View:ProposalRevision',
            'ViewAny:ProfitabilityAnalysisRevision', 'View:ProfitabilityAnalysisRevision',
            'ViewAny:SalesOrderAmendment', 'View:SalesOrderAmendment',
            'ViewAny:Comment', 'View:Comment', 'Create:Comment', 'Update:Comment', 'Delete:Comment',
            'View:CRMCluster', 'View:CRMAnalyticsPage', 'View:ProjectReviewDashboard',
            'View:SummaryProfitabilityAnalysis',
            'View:AuditProposalRevision', 'View:AuditProfitabilityAnalysisRevision', 'View:AuditAmendment',
        ];

        $financePermissions = [
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis', 'Create:ProfitabilityAnalysis', 'Update:ProfitabilityAnalysis',
            'ViewAny:SalesPlan', 'View:SalesPlan', 'Create:SalesPlan', 'Update:SalesPlan',
            'ViewAny:Lead', 'View:Lead',
            'ViewAny:ProfitabilityThreshold', 'View:ProfitabilityThreshold', 'Update:ProfitabilityThreshold',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement',
            'ViewAny:Tax', 'View:Tax',
            'ViewAny:PaymentTerm', 'View:PaymentTerm',
            'ViewAny:BankAccount', 'View:BankAccount',
            'ViewAny:Invoice', 'View:Invoice', 'Create:Invoice', 'Update:Invoice', 'SendEmail:Invoice',
            'ViewAny:SalesOrder', 'View:SalesOrder', 'Update:SalesOrder',
            'ViewAny:ProjectReview', 'View:ProjectReview',
            'ViewAny:ProfitabilityAnalysisRevision', 'View:ProfitabilityAnalysisRevision',
            'ViewAny:SalesOrderAmendment', 'View:SalesOrderAmendment',
            'ViewAny:Proposal', 'View:Proposal', 'ViewAny:ProposalRevision', 'View:ProposalRevision',
            'ViewAny:GeneralInformation', 'View:GeneralInformation',
            'ViewAny:Comment', 'View:Comment', 'Create:Comment', 'Update:Comment', 'Delete:Comment',
            'View:FinanceCluster', 'View:CRMCluster', 'View:ProjectReviewDashboard',
            'View:SummaryProfitabilityAnalysis',
            'View:AuditProposalRevision', 'View:AuditProfitabilityAnalysisRevision', 'View:AuditAmendment',
        ];

        $operationsPermissions = [
            'ViewAny:Project', 'View:Project', 'Update:Project',
            'ViewAny:Contract', 'View:Contract',
            'ViewAny:Lead', 'View:Lead',
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis',
            'ViewAny:WorkCompletionReport', 'View:WorkCompletionReport', 'Create:WorkCompletionReport', 'Update:WorkCompletionReport', 'SendEmail:WorkCompletionReport',
            'ViewAny:SalesOrder', 'View:SalesOrder',
            'ViewAny:Invoice', 'View:Invoice',
            'ViewAny:ProjectReview', 'View:ProjectReview',
            'ViewAny:ProfitabilityAnalysisRevision', 'View:ProfitabilityAnalysisRevision',
            'ViewAny:SalesOrderAmendment', 'View:SalesOrderAmendment',
            'ViewAny:Proposal', 'View:Proposal', 'ViewAny:ProposalRevision', 'View:ProposalRevision',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement',
            'ViewAny:GeneralInformation', 'View:GeneralInformation',
            'ViewAny:Comment', 'View:Comment', 'Create:Comment', 'Update:Comment', 'Delete:Comment',
            'View:ProjectCluster', 'View:CRMCluster', 'View:FinanceCluster', 'View:ProjectDashboard', 'View:ProjectReviewDashboard',
            'View:ProjectBoard', 'View:SummaryProfitabilityAnalysis',
            'View:AuditProposalRevision', 'View:AuditAmendment',
        ];

        $humanCapitalPermissions = [
            'ViewAny:Employee', 'View:Employee',
            'ViewAny:ManpowerTemplate', 'View:ManpowerTemplate',
            'ViewAny:JobPosition', 'View:JobPosition',
            'ViewAny:Lead', 'View:Lead',
            'ViewAny:ProjectReview', 'View:ProjectReview',
            'ViewAny:Comment', 'View:Comment', 'Create:Comment', 'Update:Comment', 'Delete:Comment',
            'View:CRMCluster', 'View:MasterDataCluster', 'View:ProjectReviewDashboard',
        ];

        $salesPermissions = [
            'ViewAny:Lead', 'View:Lead', 'Create:Lead', 'Update:Lead',
            'ViewAny:Proposal', 'View:Proposal', 'Create:Proposal', 'Update:Proposal', 'SendEmail:Proposal',
            'ViewAny:MinutesOfAgreement', 'View:MinutesOfAgreement', 'Create:MinutesOfAgreement', 'Update:MinutesOfAgreement',
            'ViewAny:Customer', 'View:Customer', 'Create:Customer', 'Update:Customer',
            'ViewAny:GeneralInformation', 'View:GeneralInformation', 'Create:GeneralInformation', 'Update:GeneralInformation',
            'ViewAny:SalesPlan', 'View:SalesPlan', 'Create:SalesPlan', 'Update:SalesPlan',
            'ViewAny:ProfitabilityAnalysis', 'View:ProfitabilityAnalysis', 'Create:ProfitabilityAnalysis', 'Update:ProfitabilityAnalysis',
            'ViewAny:SalesOrder', 'View:SalesOrder', 'Create:SalesOrder', 'Update:SalesOrder', 'SendEmail:SalesOrder',
            'ViewAny:Invoice', 'View:Invoice',
            'ViewAny:WorkCompletionReport', 'View:WorkCompletionReport',
            'ViewAny:ProjectReview', 'View:ProjectReview', 'Create:ProjectReview', 'Update:ProjectReview',
            'ViewAny:ManpowerTemplate', 'View:ManpowerTemplate', 'Create:ManpowerTemplate', 'Update:ManpowerTemplate',
            'ViewAny:CostingTemplate', 'View:CostingTemplate', 'Create:CostingTemplate', 'Update:CostingTemplate',
            'ViewAny:CostingTemplateItem', 'View:CostingTemplateItem', 'Create:CostingTemplateItem', 'Update:CostingTemplateItem',
            'ViewAny:ProposalRevision', 'View:ProposalRevision',
            'ViewAny:ProfitabilityAnalysisRevision', 'View:ProfitabilityAnalysisRevision',
            'ViewAny:SalesOrderAmendment', 'View:SalesOrderAmendment',
            'ViewAny:Comment', 'View:Comment', 'Create:Comment', 'Update:Comment', 'Delete:Comment',
            'ViewAny:ItemCategory', 'View:ItemCategory',
            'ViewAny:Item', 'View:Item',
            'View:CRMCluster', 'View:CRMAnalyticsPage', 'View:ProjectReviewDashboard',
            'View:SummaryProfitabilityAnalysis', 'View:MasterDataCluster',
        ];

        // Ensure all permissions are registered in the guard_name: web database first
        $allPermissions = array_unique(array_merge(
            $bodPermissions,
            $businessSupportPermissions,
            $financePermissions,
            $operationsPermissions,
            $humanCapitalPermissions,
            $salesPermissions
        ));

        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
        }

        // 2. Define standard mapping of units by code or prefix to their permissions
        $mappings = [
            // Board of Directors & Executives
            'DU' => $bodPermissions,
            'DW' => $bodPermissions,
            'DH' => array_unique(array_merge($bodPermissions, $operationsPermissions, $humanCapitalPermissions)),
            'US' => array_unique(array_merge($bodPermissions, $businessSupportPermissions)),
            'UI' => array_unique(array_merge($bodPermissions, $financePermissions, $operationsPermissions)),
            'UD' => array_unique(array_merge($bodPermissions, $businessSupportPermissions)),

            // Business Support & Marketing
            'UB' => $businessSupportPermissions,
            'UBM' => $salesPermissions,
            'UBM-1' => $salesPermissions,
            'UBS' => array_unique(array_merge($businessSupportPermissions, $operationsPermissions)),

            // Finance
            'UF' => $financePermissions,
            'UFA' => $financePermissions,
            'UFX' => $financePermissions,

            // Operations & Projects
            'UO' => $operationsPermissions,
            'UOS' => $operationsPermissions,
            'UOS-1' => $operationsPermissions,
            'UOG' => $operationsPermissions,
            'UOM' => $operationsPermissions,
            'USP' => array_unique(array_merge($operationsPermissions, $businessSupportPermissions)),
            'DB' => array_unique(array_merge($operationsPermissions, $humanCapitalPermissions)),

            // Human Capital
            'UH' => $humanCapitalPermissions,
            'UHC' => $humanCapitalPermissions,
            'UHC-1' => $humanCapitalPermissions,
            'UHC-2' => array_unique(array_merge($humanCapitalPermissions, $operationsPermissions)),
            'UHD' => $humanCapitalPermissions,
            'USL' => array_unique(array_merge($humanCapitalPermissions, $businessSupportPermissions)),
        ];

        // 3. Assign permissions to units dynamically
        $units = Unit::all();

        foreach ($units as $unit) {
            $code = $unit->code;
            if (empty($code)) {
                continue;
            }

            // Exact match
            if (isset($mappings[$code])) {
                $unit->syncPermissions($mappings[$code]);
                $this->command->info("Synced permissions for unit: {$unit->name} ({$code})");

                continue;
            }

            // Fallback: Prefix match (e.g. UHC-1 matches UHC)
            $prefix = explode('-', $code)[0];
            if (isset($mappings[$prefix])) {
                $unit->syncPermissions($mappings[$prefix]);
                $this->command->info("Synced permissions for unit via prefix [{$prefix}]: {$unit->name} ({$code})");

                continue;
            }

            // General fallback based on name analysis
            $nameLower = strtolower($unit->name);
            if (str_contains($nameLower, 'director') || str_contains($nameLower, 'board')) {
                $unit->syncPermissions($bodPermissions);
                $this->command->info("Synced BOD permissions for unit based on name: {$unit->name} ({$code})");
            } elseif (str_contains($nameLower, 'finance') || str_contains($nameLower, 'accounting') || str_contains($nameLower, 'treasury') || str_contains($nameLower, 'tax')) {
                $unit->syncPermissions($financePermissions);
                $this->command->info("Synced Finance permissions for unit based on name: {$unit->name} ({$code})");
            } elseif (str_contains($nameLower, 'operation') || str_contains($nameLower, 'security') || str_contains($nameLower, 'procurement')) {
                $unit->syncPermissions($operationsPermissions);
                $this->command->info("Synced Operations permissions for unit based on name: {$unit->name} ({$code})");
            } elseif (str_contains($nameLower, 'human capital') || str_contains($nameLower, 'hc') || str_contains($nameLower, 'payroll')) {
                $unit->syncPermissions($humanCapitalPermissions);
                $this->command->info("Synced Human Capital permissions for unit based on name: {$unit->name} ({$code})");
            } elseif (str_contains($nameLower, 'marketing') || str_contains($nameLower, 'sales') || str_contains($nameLower, 'support')) {
                $unit->syncPermissions($businessSupportPermissions);
                $this->command->info("Synced Business Support permissions for unit based on name: {$unit->name} ({$code})");
            }
        }

        $this->command->info('Unit permissions seeding completed successfully.');
    }
}
