<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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
use Modules\MasterData\Models\Unit;
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
            'Account Manager & Sales' => Role::where('name', 'Account Manager & Sales')->value('id'),
            'Project Manager' => Role::firstOrCreate(['name' => 'Project Manager'], ['guard_name' => 'web'])->id,
        ];

        // Unit UUID Lookups with robust fallbacks
        $unitIds = [
            'DU' => Unit::where('code', 'DU')->value('id') ?? Unit::where('name', 'like', '%Director%')->value('id'),
            'UF' => Unit::where('code', 'UF')->value('id') ?? Unit::where('name', 'like', '%Finance%')->value('id'),
            'UB' => Unit::where('code', 'UB')->value('id') ?? Unit::where('name', 'like', '%Business Support%')->value('id'),
            'UO' => Unit::where('code', 'UO')->value('id') ?? Unit::where('name', 'like', '%Operation%')->value('id'),
            'UH' => Unit::where('code', 'UH')->where('name', 'Human Capital Management')->value('id') ?? Unit::where('name', 'like', '%Human Capital%')->value('id'),
        ];

        // Specific Group Head User Lookups with fallback to firstOrCreate to guarantee UUIDs exist
        $userIds = [
            'UB' => User::where('email', 'a.syifa@garudapratama.com')->value('id') ?? User::firstOrCreate(['email' => 'a.syifa@garudapratama.com'], [
                'name' => 'Achmad Syifa',
                'employee_code' => '9500159',
                'password' => Hash::make('gdps2019!'),
            ])->id,
            'UF' => User::where('email', 'theresia@garudapratama.com')->value('id') ?? User::firstOrCreate(['email' => 'theresia@garudapratama.com'], [
                'name' => 'Theresia',
                'employee_code' => '9500232',
                'password' => Hash::make('gdps2019!'),
            ])->id,
            'UO' => User::where('email', 'd.anton@garudapratama.com')->value('id') ?? User::firstOrCreate(['email' => 'd.anton@garudapratama.com'], [
                'name' => 'Dartin Anton',
                'employee_code' => '9500060',
                'password' => Hash::make('gdps2019!'),
            ])->id,
            'UH' => User::where('email', 'wiwied@garudapratama.com')->value('id') ?? User::firstOrCreate(['email' => 'wiwied@garudapratama.com'], [
                'name' => 'Wiwied Widyasmara Adi',
                'employee_code' => '9500184',
                'password' => Hash::make('gdps2019!'),
            ])->id,
            'DU' => User::where('email', 'cornelis@garudapratama.com')->value('id') ?? User::firstOrCreate(['email' => 'cornelis@garudapratama.com'], [
                'name' => 'Cornelis Radjawane',
                'employee_code' => '9500001',
                'password' => Hash::make('gdps2019!'),
            ])->id,
        ];

        // Remove General Information rules as they are no longer approved by UB — creator signs directly
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
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Purchase Order Rules
            [
                'resource_type' => PurchaseOrder::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Work Order Rules
            [
                'resource_type' => WorkOrder::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
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
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UF']])),
                'signature_type' => 'MarginApproval',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UB']])),
                'signature_type' => 'MarginApproval',
                'order' => 2,
                'is_active' => true,
            ],
            // Beyond Care -> User HC (Margin)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => (string) $beyondCareId],
                ],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UH']])),
                'signature_type' => 'MarginApproval',
                'order' => 3,
                'is_active' => true,
            ],
            // Other Beyond -> User Operations (Margin)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => $beyondOpsIds],
                ],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UO']])),
                'signature_type' => 'MarginApproval',
                'order' => 4,
                'is_active' => true,
            ],

            /**
             * Profitability Analysis - PA Approval Step (after Margin)
             */
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UF']])),
                'signature_type' => 'Approver',
                'order' => 10,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 11,
                'is_active' => true,
            ],
            // Beyond Care -> User HC (PA)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => '=', 'value' => (string) $beyondCareId],
                ],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UH']])),
                'signature_type' => 'Approver',
                'order' => 12,
                'is_active' => true,
            ],
            // Other Beyond -> User Operations (PA)
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [
                    ['field' => 'product_cluster_id', 'operator' => 'in', 'value' => $beyondOpsIds],
                ],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['UO']])),
                'signature_type' => 'Approver',
                'order' => 13,
                'is_active' => true,
            ],
            [
                'resource_type' => ProfitabilityAnalysis::class,
                'conditions' => [],
                'approver_type' => 'User',
                'approver_user_id' => array_values(array_filter([$userIds['DU']])),
                'signature_type' => 'Approver',
                'order' => 14,
                'is_active' => true,
            ],
            // Sales Order Rules
            [
                'resource_type' => SalesOrder::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            // Sales Order Amendment Rules
            [
                'resource_type' => SalesOrderAmendment::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],

            // Invoice Rules
            [
                'resource_type' => Invoice::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UF']])),
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
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
                'signature_type' => 'Approver',
                'order' => 1,
                'is_active' => true,
            ],
            // Logistics - Purchase Order
            [
                'resource_type' => LogisticsPurchaseOrder::class,
                'conditions' => [],
                'approver_type' => 'Unit',
                'approver_unit_id' => array_values(array_filter([$unitIds['UB']])),
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
                    'approver_role' => $rule['approver_role'] ?? null,
                    'approver_unit_id' => $rule['approver_unit_id'] ?? null,
                    'approver_user_id' => $rule['approver_user_id'] ?? null,
                    'approver_position' => $rule['approver_position'] ?? null,
                    'is_active' => $rule['is_active'],
                ]
            );
        }
    }
}
