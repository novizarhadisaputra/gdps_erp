<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schema = config('database.default') === 'sqlite' ? 'master_data_' : 'master_data.';

        $accounts = [
            [
                'bank_name' => 'Bank Mandiri',
                'account_number' => '1234567890',
                'account_name' => 'PT Garuda Daya Pratama Sejahtera - Operational',
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'bank_name' => 'Bank BNI',
                'account_number' => '0987654321',
                'account_name' => 'PT Garuda Daya Pratama Sejahtera - Internal',
                'currency' => 'IDR',
                'is_active' => true,
            ],
            [
                'bank_name' => 'Bank BCA',
                'account_number' => '1122334455',
                'account_name' => 'PT Garuda Daya Pratama Sejahtera - Payroll',
                'currency' => 'IDR',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $acc) {
            $existing = DB::table($schema.'bank_accounts')->where('account_number', $acc['account_number'])->first();

            if ($existing) {
                DB::table($schema.'bank_accounts')->where('id', $existing->id)->update(array_merge($acc, [
                    'updated_at' => now(),
                ]));
            } else {
                DB::table($schema.'bank_accounts')->insert(array_merge($acc, [
                    'id' => (string) Str::uuid(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
