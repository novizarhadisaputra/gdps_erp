<?php

namespace Modules\Finance\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Models\ChartOfAccount;

class SapChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            // Cash and Cash Equivalents
            ['10000064', 'BS', 'PETTY CASH GDPS-IDR', 'Cash and Cash Equivalents'],
            ['10002310', 'BS', 'BANK MANDIRI-IDR', 'Cash and Cash Equivalents'],
            ['10002314', 'BS', 'BANK MANDIRI-TABUNGAN BISNIS', 'Cash and Cash Equivalents'],
            ['10002404', 'BS', 'BANK BNI GIRO-IDR', 'Cash and Cash Equivalents'],
            ['11002500', 'BS', 'GDPS BRI GIRO-IDR-144201000003304', 'Cash and Cash Equivalents'],
            ['11002010', 'BS', 'BANK BNI GIRO-IDR-2201020195', 'Cash and Cash Equivalents'],
            ['11002014', 'BS', 'BANK BCA GIRO-IDR-8681037942', 'Cash and Cash Equivalents'],

            // AR Trade Third Parties
            ['10017200', 'BS', 'ACCOUNT RECEIVABLE-TP-DOMESTIC', 'AR Trade Third Parties'],
            ['10019850', 'BS', 'ALW. BDE - A/R FROM TP-PSAK 71', 'AR Trade Third Parties'],

            // Accrued Revenue Third Parties
            ['10018905', 'BS', 'ACCRUED REVENUE THIRD PARTIES', 'Accrued Revenue Third Parties'],
            ['20057900', 'BS', 'PROVISION FOR DOUBTFUL PROGRESS BILLING-TP-INT', 'Accrued Revenue Third Parties'],

            // AR Trade Related Parties
            ['11010012', 'BS', 'ACCOUNT RECEIVABLE - GA HODI', 'AR Trade Related Parties'],
            ['11010014', 'BS', 'ACCOUNT RECEIVABLE - GA HOCH', 'AR Trade Related Parties'],
            ['11010188', 'BS', 'ACCOUNT RECEIVABLE - GA DPS0', 'AR Trade Related Parties'],
            ['11010202', 'BS', 'ACCOUNT RECEIVABLE - GA SUB0', 'AR Trade Related Parties'],
            ['11012060', 'BS', 'ACCOUNT RECEIVABLE-CT00', 'AR Trade Related Parties'],
            ['11014000', 'BS', 'ACCOUNT RECEIVABLE-AC00', 'AR Trade Related Parties'],
            ['11019050', 'BS', 'ACCOUNT RECEIVABLE-GMF', 'AR Trade Related Parties'],

            // Accrued Revenue Related Parties
            ['11018000', 'BS', 'ACCRUED REVENUE-GMF', 'Accrued Revenue Related Parties'],
            ['11018032', 'BS', 'ACCRUED REVENUE-CITILINK', 'Accrued Revenue Related Parties'],
            ['11018124', 'BS', 'ACCRUED REVENUE- GA CGK0', 'Accrued Revenue Related Parties'],
            ['11018190', 'BS', 'ACCRUED REVENUE- GA JOG0', 'Accrued Revenue Related Parties'],
            ['11018188', 'BS', 'ACCRUED REVENUE- GA DPS0', 'Accrued Revenue Related Parties'],
            ['11018202', 'BS', 'ACCRUED REVENUE- GA SUB0', 'Accrued Revenue Related Parties'],
            ['11018700', 'BS', 'ACCRUED REVENUE-ASY0', 'Accrued Revenue Related Parties'],

            // Revenue
            ['30004100', 'PL', 'REVENUE -GARUDA-HODI', 'Revenue from GA Group'],
            ['31005009', 'PL', 'REVENUE CITILINK', 'Revenue from GA Group'],
            ['31009000', 'PL', 'REVENUE TMB GDPS FROM GMF', 'Revenue from GMF'],
            ['31009501', 'PL', 'REVENUE THIRD PARTY-OTHERS', 'Revenue from Third Parties'],
            ['30015011', 'PL', 'REVENUE TMB THIRD PARTY', 'Revenue from Third Parties'],

            // Expenses
            ['40004000', 'PL', 'BASE SALARY-MANPE', 'Manpower Expenses'],
            ['40004104', 'PL', 'OVERTIME ALLOWANCE-MANPE', 'Manpower Expenses'],
            ['41000100', 'PL', 'SHIFT ALLOWANCE - MANPE', 'Manpower Expenses'],
            ['40044009', 'PL', 'MANAGEMENT EXPENSE', 'Operating Expenses'],

            // Taxes
            ['20003100', 'BS', 'WITHOLDING TAX PAYABLE ARTICLE 21', 'Taxes Payable'],
            ['20003300', 'BS', 'WITHOLDING TAX PAYABLE ARTICLE 23', 'Taxes Payable'],
            ['20003700', 'BS', 'ACCOUNT PAYABLE-VALUE ADDED TAX OUT', 'Taxes Payable'],
        ];

        $parents = [];

        foreach ($data as $row) {
            $code = $row[0];
            $type = $row[1] === 'BS' ? 'Balance Sheet' : 'Profit & Loss';
            $name = $row[2];
            $groupName = $row[3];

            // Ensure parent exists
            if (! isset($parents[$groupName])) {
                $parent = ChartOfAccount::updateOrCreate([
                    'name' => $groupName,
                    'parent_id' => null,
                ], [
                    'code' => 'GRP-'.strtoupper(str_replace(' ', '_', $groupName)),
                    'account_type' => $type,
                    'is_active' => true,
                ]);
                $parents[$groupName] = $parent->id;
            }

            // Create account
            ChartOfAccount::updateOrCreate([
                'code' => $code,
            ], [
                'name' => $name,
                'account_type' => $type,
                'parent_id' => $parents[$groupName],
                'is_active' => true,
            ]);
        }
    }
}
