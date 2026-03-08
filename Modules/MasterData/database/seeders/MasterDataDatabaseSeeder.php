<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;

class MasterDataDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            RemunerationParameterSeeder::class,
            TaxRateTerSeeder::class,
            MasterCodeSeeder::class,
            ApprovalRuleSeeder::class,
            ContactRoleSeeder::class,
            AssetGroupSeeder::class,
            JobPositionSeeder::class,
            RegencyMinimumWageSeeder::class,
            UnitSyncSeeder::class,
            ItemSeeder::class,
            JkkConfigSeeder::class,
            JkmConfigSeeder::class,
            JhtConfigSeeder::class,
            JpConfigSeeder::class,
            HealthConfigSeeder::class,
            DirectCostCategorySeeder::class,
        ]);
    }
}
