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
            MasterCodeSeeder::class,
            ApprovalRuleSeeder::class,
            ContactRoleSeeder::class,
            AssetGroupSeeder::class,
            JobPositionSeeder::class,
            BpjsConfigSeeder::class,
            RegencyMinimumWageSeeder::class,
            UnitSyncSeeder::class,
        ]);
    }
}
