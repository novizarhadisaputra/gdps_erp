<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Services\UnitService;

class UnitSyncSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /** @var UnitService $service */
        $service = app(UnitService::class);

        $this->command->info('Syncing units from external API...');

        $synced = $service->syncFromApi();

        $this->command->info("Successfully synced {$synced->count()} units.");
    }
}
