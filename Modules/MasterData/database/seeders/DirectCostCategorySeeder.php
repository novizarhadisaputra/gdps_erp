<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DirectCostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schema = config('database.default') === 'sqlite' ? '' : 'master_data.';

        $categories = [
            ['code' => 'manpower', 'name' => 'Manpower', 'type' => 'direct'],
            ['code' => 'tools_equipment', 'name' => 'Tools & Equipment', 'type' => 'direct'],
            ['code' => 'material', 'name' => 'Material', 'type' => 'direct'],
            ['code' => 'it_system', 'name' => 'IT System', 'type' => 'direct'],
            ['code' => 'warranty', 'name' => 'Warranty', 'type' => 'direct'],
            ['code' => 'infrastructure', 'name' => 'Infrastructure Support', 'type' => 'direct'],
            ['code' => 'others', 'name' => 'Others', 'type' => 'direct'],
            // Indirect Categories
            ['code' => 'indirect_mgmt', 'name' => 'Management Expense', 'type' => 'indirect'],
            ['code' => 'indirect_entertainment', 'name' => 'Entertainment', 'type' => 'indirect'],
            ['code' => 'indirect_bp', 'name' => 'Business Partner', 'type' => 'indirect'],
            ['code' => 'indirect_concession', 'name' => 'Concession', 'type' => 'indirect'],
        ];

        foreach ($categories as $cat) {
            DB::table($schema.'direct_cost_categories')->updateOrInsert(
                ['code' => $cat['code']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
