<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\Province;
use Modules\MasterData\Models\Regency;
use Modules\MasterData\Services\WilayahSyncService;

class ProvincialRegencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $syncService = new WilayahSyncService();

        // 1. Sync Provinces (Level 1)
        if ($this->command) {
            $this->command->info('Syncing Provinces from wilayah.id...');
        }
        $syncService->syncProvinces();

        // 2. Sync Regencies/Cities (Level 2)
        if ($this->command) {
            $this->command->info('Syncing Regencies from wilayah.id...');
        }
        $syncService->syncAllRegencies();
        
        $this->command->info('Geographic data synced successfully.');
    }
}
