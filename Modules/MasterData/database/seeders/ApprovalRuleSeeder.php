<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\ApprovalRule;

class ApprovalRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // General Information Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\GeneralInformation',
                'criteria_field' => 'sequence_number',
                'operator' => '>=',
                'value' => 0,
                'approver_type' => 'Role',
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
                'order' => 1,
                'is_active' => true,
            ],
            // Profitability Analysis Rules - Always applies
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'criteria_field' => 'revenue_per_month',
                'operator' => '>=',
                'value' => 0,
                'approver_type' => 'Role',
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
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
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
                'order' => 1,
                'is_active' => true,
            ],
            // Minutes of Agreement Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\MinutesOfAgreement',
                'criteria_field' => null,
                'operator' => null,
                'value' => null,
                'approver_type' => 'Role',
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
                'order' => 1,
                'is_active' => true,
            ],
            // Contract Rules - Always applies
            [
                'resource_type' => 'Modules\CRM\Models\Contract',
                'criteria_field' => null,
                'operator' => null,
                'value' => null,
                'approver_type' => 'Role',
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
                'order' => 1,
                'is_active' => true,
            ],
            // Beyond Care -> VP HC
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    [
                        'field' => 'product_cluster_id',
                        'operator' => '=',
                        'value' => '019ceb55-38ab-7290-b098-e098ca7eff68',
                    ],
                ],
                'approver_type' => 'Role',
                'approver_role' => ['VP Human Capital'],
                'signature_type' => 'approval',
                'order' => 10,
                'is_active' => true,
            ],
            // Beyond Facility, Beyond Clean, Beyond Secure, Beyond Sky -> VP Ops
            [
                'resource_type' => 'Modules\Finance\Models\ProfitabilityAnalysis',
                'conditions' => [
                    [
                        'field' => 'product_cluster_id',
                        'operator' => 'in',
                        'value' => '019ceb55-38ad-7203-b423-1fa7d0d1a698,019ceb55-38ab-7290-b098-e098cb3789d8,019ceb55-38ac-734d-8bdb-274638170a7c,019ceb55-38ad-7203-b423-1fa7cfd8e67a',
                    ],
                ],
                'approver_type' => 'Role',
                'approver_role' => ['VP Ops'],
                'signature_type' => 'approval',
                'order' => 10,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalRule::updateOrCreate(
                [
                    'resource_type' => $rule['resource_type'],
                    'order' => $rule['order'],
                ],
                $rule
            );
        }
    }
}
