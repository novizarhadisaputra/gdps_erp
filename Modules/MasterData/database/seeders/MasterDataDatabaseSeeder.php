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
            ProvincialRegencySeeder::class, // Sync official data first
            MasterCodeSeeder::class, // Manual codes now check against synced data
            OfficialMinimumWageSeeder::class, // Layer wage data
            RemunerationParameterSeeder::class,
            UnitSyncSeeder::class,
            ApprovalRuleSeeder::class,
            ContactRoleSeeder::class,
            AssetGroupSeeder::class,
            JobPositionSeeder::class,
            UnitPermissionSeeder::class,
            ItemSeeder::class,
            JkkConfigSeeder::class,
            JkmConfigSeeder::class,
            JhtConfigSeeder::class,
            JpConfigSeeder::class,
            HealthConfigSeeder::class,
            DirectCostCategorySeeder::class,
            TaxTerRateSeeder::class,
            TaxPasal17RateSeeder::class,
            ApiClientSeeder::class,
            ProductClusterLogoSeeder::class,
            AppSettingSeeder::class,
            RevenueTypeSeeder::class,
            BankAccountSeeder::class,
            TrainingSeeder::class,
            TaxObjectSeeder::class,
        ]);
    }
}
