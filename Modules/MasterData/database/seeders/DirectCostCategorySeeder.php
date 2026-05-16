<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DirectCostCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schema = config('database.default') === 'sqlite' ? 'master_data_' : 'master_data.';

        $categories = [
            ['id' => '2d78e9a4-ea83-48dc-8772-d18751b145bc', 'code' => 'manpower', 'name' => 'Manpower', 'type' => 'direct'],
            ['id' => '30106448-d05d-4b71-bd24-22f2ee5bdbe5', 'code' => 'tools_equipment', 'name' => 'Tools & Equipment', 'type' => 'direct'],
            ['id' => '11448973-0f59-476d-855d-b04968e99799', 'code' => 'material', 'name' => 'Material', 'type' => 'direct'],
            ['id' => '4b4f4248-7bfe-45d0-9c5e-7bca1d80a391', 'code' => 'it_system', 'name' => 'IT System', 'type' => 'direct'],
            ['id' => '0dcb5a91-0e9a-49f8-9836-f4a8a73e4a22', 'code' => 'warranty', 'name' => 'Warranty', 'type' => 'direct'],
            ['id' => 'ab360459-f646-4207-9218-ca00c0cb375a', 'code' => 'infrastructure', 'name' => 'Infrastructure Support', 'type' => 'direct'],
            ['id' => '99603031-a13e-4072-b945-7de83efd6d33', 'code' => 'others', 'name' => 'Others', 'type' => 'direct'],
            // Indirect Categories
            ['id' => 'bd060787-4ac3-49cd-a0ba-f182ecc0edb7', 'code' => 'indirect_mgmt', 'name' => 'Management Expense', 'type' => 'indirect'],
            ['id' => '70addba5-664e-45e9-9330-f8802e0bb78f', 'code' => 'indirect_entertainment', 'name' => 'Entertainment', 'type' => 'indirect'],
            ['id' => '5d721462-a457-431b-ab83-b31e2e832e2f', 'code' => 'indirect_bp', 'name' => 'Business Partner', 'type' => 'indirect'],
            ['id' => 'c0759fbb-6117-49ad-a448-3d65045ba854', 'code' => 'indirect_concession', 'name' => 'Concession', 'type' => 'indirect'],
        ];

        foreach ($categories as $cat) {
            DB::table($schema.'direct_cost_categories')->updateOrInsert(
                ['code' => $cat['code']],
                [
                    'id' => $cat['id'],
                    'name' => $cat['name'],
                    'type' => $cat['type'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
