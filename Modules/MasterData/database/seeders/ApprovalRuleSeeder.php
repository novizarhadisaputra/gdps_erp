<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Modules\CRM\Models\CooperationAgreement;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\MinutesOfAgreement;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\PurchaseOrder;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\CRM\Models\WorkOrder;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Logistics\Models\PurchaseOrder as LogisticsPurchaseOrder;
use Modules\Logistics\Models\PurchaseRequest as LogisticsPurchaseRequest;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\ProductCluster;
use Modules\Project\Models\WorkCompletionReport;

class ApprovalRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Get product cluster IDs dynamically
        $beyondCareId = ProductCluster::where('code', 'BCA')->value('id');
        $beyondFacilityId = ProductCluster::where('code', 'BFM')->value('id');
        $beyondCleanId = ProductCluster::where('code', 'BCL')->value('id');
        $beyondSecureId = ProductCluster::where('code', 'BSE')->value('id');
        $beyondSkyId = ProductCluster::where('code', 'BSK')->value('id');

        $beyondOpsIds = array_filter([$beyondFacilityId, $beyondCleanId, $beyondSecureId, $beyondSkyId]);

        // Role UUID Lookups
        $roleIds = [
            'super_admin' => Role::where('name', 'super_admin')->value('id'),
            'VP Finance' => Role::where('name', 'VP Finance')->value('id'),
            'VP Business Support' => Role::where('name', 'VP Business Support')->value('id'),
            'VP Operations' => Role::where('name', 'VP Operations')->value('id'),
            'VP Human Capital' => Role::where('name', 'VP Human Capital')->value('id'),
            'Board of Directors' => Role::where('name', 'Board of Directors')->value('id'),
            'Account Manager & Sales' => Role::where('name', 'Account Manager & Sales')->value('id'),
            'Project Manager' => Role::firstOrCreate(['name' => 'Project Manager'], ['guard_name' => 'web'])->id,
        ];

        // ApprovalRule::truncate(); // Removed to prevent wiping user-defined rules

        // Remove General Information rules as they are no longer approved by VP Business Support
        ApprovalRule::where('resource_type', GeneralInformation::class)->delete();

        // Remove Proposal rules as they are no longer approved by role — creator signs directly
        ApprovalRule::where('resource_type', Proposal::class)->delete();

        $rules = [
            // Minutes of Agreement Rules
            [
                'resource_type' => MinutesOfAgreement::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['super_admin']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Cooperation Agreement Rules
            [
                'resource_type' => CooperationAgreement::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Purchase Order Rules
            [
                'resource_type' => PurchaseOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Work Order Rules
            [
                'resource_type' => WorkOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - Margin Approval Step
             */
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'MarginApproval',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'MarginApproval',
                'order' => 2,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (Margin)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => (string) $beyondCareId],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Human Capital']])),
                'signature_type' => 'MarginApproval',
                'order' => 3,
                'is_active' => true,
            ],
            // Other Beyond -> VP Operations (Margin)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => $beyondOpsIds],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Operations']])),
                'signature_type' => 'MarginApproval',
                'order' => 4, // Changed order to prevent collision if needed, but truncate fixes it
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - PA Approval Step (after Margin)
             */
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 11,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (PA)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => (string) $beyondCareId],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Human Capital']])),
                'signature_type' => 'Approver',
                'order' => 12,
                'is_active' => true,
            ],
            // Other Beyond -> VP Operations (PA)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => $beyondOpsIds],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Operations']])),
                'signature_type' => 'Approver',
                'order' => 13,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['Board of Directors']])),
                'signature_type' => 'Approver',
                'order' => 14,
                'is_active' => true,
            ],
            // Sales Order Rules
            [
                'resource_type' => SalesOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            // Sales Order Amendment Rules
            [
                'resource_type' => SalesOrderAmendment::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            // Invoice Rules
            [
                'resource_type' => Invoice::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            // Work Completion Report (BAPP) Approvals
            // Requirement: Assigned Project Manager (Oprep)
            [
                'resource_type' => WorkCompletionReport::class,
                'conditions' => [],
                'approver_type' => 'Relationship',
                'approver_role' => ['project.oprep'],
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Logistics - Purchase Request
            [
                'resource_type' => LogisticsPurchaseRequest::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Logistics - Purchase Order
            [
                'resource_type' => LogisticsPurchaseOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalRule::updateOrCreate(
                [
                    'resource_type' => $rule['resource_type'],
                    'signature_type' => $rule['signature_type'],
                    'order' => $rule['order'],
                ],
                [
                    'conditions' => $rule['conditions'],
                    'approver_type' => $rule['approver_type'],
                    'approver_role' => $rule['approver_role'],
                    'is_active' => $rule['is_active'],
                ]
            );
        }
    }
}
