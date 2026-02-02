<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\ApprovalRule;

class ApprovalRuleSeeder extends Seeder
{
    public function run(): void
    {
        $rules = [
            // Profitability Analysis Rules
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
            // Proposal Rules
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
            // Contract Rules
            [
                'resource_type' => 'Modules\CRM\Models\Contract',
                'criteria_field' => 'contract_number',
                'operator' => '!=',
                'value' => 0,
                'approver_type' => 'Role',
                'approver_role' => ['super_admin'],
                'signature_type' => 'approval',
                'order' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ApprovalRule::updateOrCreate(
                [
                    'resource_type' => $rule['resource_type'],
                    'criteria_field' => $rule['criteria_field'],
                    'order' => $rule['order'],
                ],
                $rule
            );
        }
    }
}
