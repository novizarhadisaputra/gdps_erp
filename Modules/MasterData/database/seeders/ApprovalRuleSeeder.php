<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\ProductCluster;

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
        ];

        ApprovalRule::truncate();

        $rules = [
            // General Information Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\GeneralInformation',
                'conditions' => [
                    ['field' => 'sequence_number', 'operator' => '>=', 'value' => 0],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['super_admin']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Proposal Rules - Mirrors PA Approval Step
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => (string) $beyondCareId],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Human Capital']])),
                'signature_type' => 'Approver',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => $beyondOpsIds],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Operations']])),
                'signature_type' => 'Approver',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['Board of Directors']])),
                'signature_type' => 'Approver',
                'order' => 5,
                'is_active' => true,
            ],
            // Minutes of Agreement Rules
            [
                'resource_type' => 'Modules\CRM\Models\MinutesOfAgreement',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['super_admin']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Contract Rules
            [
                'resource_type' => 'Modules\CRM\Models\Contract',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['super_admin']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - Margin Approval Step
             */
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'MarginApproval',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'MarginApproval',
                'order' => 2,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (Margin)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
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
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
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
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 11,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (PA)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
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
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
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
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['Board of Directors']])),
                'signature_type' => 'Approver',
                'order' => 14,
                'is_active' => true,
            ],
            // Sales Order Rules - Mirroring Proposal & PA Final Step
            [
                'resource_type' => SalesOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => SalesOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'resource_type' => SalesOrder::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['Board of Directors']])),
                'signature_type' => 'Approver',
                'order' => 3,
                'is_active' => true,
            ],

            // Sales Order Amendment Rules
            [
                'resource_type' => SalesOrderAmendment::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Finance']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => SalesOrderAmendment::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['VP Business Support']])),
                'signature_type' => 'Approver',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'resource_type' => SalesOrderAmendment::class,
                'conditions' => [],
                'approver_type' => 'Role',
                'approver_role' => array_values(array_filter([$roleIds['Board of Directors']])),
                'signature_type' => 'Approver',
                'order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalRule::create($rule);
        }
    }
}
