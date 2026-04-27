<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1000',
                'name' => 'ASSETS',
                'account_type' => 'Asset',
                'children' => [
                    [
                        'code' => '1100',
                        'name' => 'CURRENT ASSETS',
                        'account_type' => 'Asset',
                        'children' => [
                            ['code' => '1101', 'name' => 'Cash in Bank', 'account_type' => 'Asset'],
                            ['code' => '1102', 'name' => 'Petty Cash', 'account_type' => 'Asset'],
                            ['code' => '1200', 'name' => 'Accounts Receivable', 'account_type' => 'Asset'],
                        ],
                    ],
                    [
                        'code' => '1500',
                        'name' => 'FIXED ASSETS',
                        'account_type' => 'Asset',
                        'children' => [
                            ['code' => '1501', 'name' => 'Equipment', 'account_type' => 'Asset'],
                            ['code' => '1502', 'name' => 'Vehicles', 'account_type' => 'Asset'],
                        ],
                    ],
                ],
            ],
            [
                'code' => '2000',
                'name' => 'LIABILITIES',
                'account_type' => 'Liability',
                'children' => [
                    ['code' => '2100', 'name' => 'Accounts Payable', 'account_type' => 'Liability'],
                    ['code' => '2200', 'name' => 'Taxes Payable', 'account_type' => 'Liability'],
                ],
            ],
            [
                'code' => '3000',
                'name' => 'EQUITY',
                'account_type' => 'Equity',
                'children' => [
                    ['code' => '3100', 'name' => 'Capital Stock', 'account_type' => 'Equity'],
                    ['code' => '3200', 'name' => 'Retained Earnings', 'account_type' => 'Equity'],
                ],
            ],
            [
                'code' => '4000',
                'name' => 'REVENUE',
                'account_type' => 'Revenue',
                'children' => [
                    ['code' => '4100', 'name' => 'Sales Revenue', 'account_type' => 'Revenue'],
                    ['code' => '4200', 'name' => 'Service Revenue', 'account_type' => 'Revenue'],
                ],
            ],
            [
                'code' => '5000',
                'name' => 'EXPENSES',
                'account_type' => 'Expense',
                'children' => [
                    ['code' => '5100', 'name' => 'Salary Expenses', 'account_type' => 'Expense'],
                    ['code' => '5200', 'name' => 'Utility Expenses', 'account_type' => 'Expense'],
                    ['code' => '5300', 'name' => 'Rent Expenses', 'account_type' => 'Expense'],
                ],
            ],
        ];

        foreach ($accounts as $accountData) {
            $this->createAccount($accountData);
        }
    }

    private function createAccount(array $data, $parentId = null)
    {
        $children = $data['children'] ?? [];
        unset($data['children']);

        $data['parent_id'] = $parentId;
        $account = ChartOfAccount::create($data);

        foreach ($children as $childData) {
            $this->createAccount($childData, $account->id);
        }
    }
}
