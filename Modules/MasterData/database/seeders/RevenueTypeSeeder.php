<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RevenueTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schema = config('database.default') === 'sqlite' ? 'master_data_' : 'master_data.';

        $types = [
            ['name' => 'Manpower & Management Fee', 'code' => 'manpower', 'to' => ['revenue', 'expense']],
            ['name' => 'Overtime', 'code' => 'overtime', 'to' => ['revenue', 'expense']],
            ['name' => 'Material', 'code' => 'material', 'to' => ['revenue', 'expense']],
            ['name' => 'Other Direct Cost', 'code' => 'other_direct', 'to' => ['expense']],
            ['name' => 'Indirect Cost', 'code' => 'indirect', 'to' => ['expense']],
        ];

        foreach ($types as $type) {
            $existing = DB::table($schema.'revenue_types')->where('code', $type['code'])->first();

            if ($existing) {
                DB::table($schema.'revenue_types')->where('id', $existing->id)->update([
                    'name' => $type['name'],
                    'is_active' => true,
                    'is_default' => $type['code'] === 'manpower',
                    'applicable_to' => json_encode($type['to']),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table($schema.'revenue_types')->insert([
                    'id' => (string) Str::uuid(),
                    'name' => $type['name'],
                    'code' => $type['code'],
                    'is_active' => true,
                    'is_default' => $type['code'] === 'manpower',
                    'applicable_to' => json_encode($type['to']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
