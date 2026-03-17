<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
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
        ];

        $rules = [
            // General Information Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\GeneralInformation',
                'criteria_field' => 'sequence_number',
                'operator' => '>=',
                'value' => 0,
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['super_admin']]),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Proposal Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\Proposal',
                'criteria_field' => 'amount',
                'operator' => '>=',
                'value' => 0,
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['super_admin']]),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Minutes of Agreement Rules
            [
                'resource_type' => 'Modules\CRM\Models\MinutesOfAgreement',
                'criteria_field' => null,
                'operator' => null,
                'value' => null,
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['super_admin']]),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Contract Rules
            [
                'resource_type' => 'Modules\CRM\Models\Contract',
                'criteria_field' => null,
                'operator' => null,
                'value' => null,
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['super_admin']]),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - Margin Approval Step
             */
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Finance']]),
                'signature_type' => 'MarginApproval',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Business Support']]),
                'signature_type' => 'MarginApproval',
                'order' => 2,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (Margin)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => $beyondCareId],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Human Capital']]),
                'signature_type' => 'MarginApproval',
                'order' => 3,
                'is_active' => true,
            ],
            // Other Beyond -> VP Operations (Margin)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => implode(',', $beyondOpsIds)],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Operations']]),
                'signature_type' => 'MarginApproval',
                'order' => 3,
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - PA Approval Step (after Margin)
             */
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Finance']]),
                'signature_type' => 'Approver',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Business Support']]),
                'signature_type' => 'Approver',
                'order' => 11,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC (PA)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => $beyondCareId],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Human Capital']]),
                'signature_type' => 'Approver',
                'order' => 12,
                'is_active' => true,
            ],
            // Other Beyond -> VP Operations (PA)
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => implode(',', $beyondOpsIds)],
                ],
                'approver_type' => 'Role',
                'approver_role' => array_filter([$roleIds['VP Operations']]),
                'signature_type' => 'Approver',
                'order' => 12,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalRule::updateOrCreate(
                [
                    'resource_type' => $rule['resource_type'],
                    'signature_type' => $rule['signature_type'] ?? 'approval',
                    'order' => $rule['order'],
                ],
                $rule
            );
        }
    }
}
