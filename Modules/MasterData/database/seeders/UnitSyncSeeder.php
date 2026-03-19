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
        /** @var \App\Services\SsoAuthService $authService */
        $authService = app(\App\Services\SsoAuthService::class);
        /** @var UnitService $unitService */
        $unitService = app(UnitService::class);

        $this->command->info('Attempting SSO login for Unit Sync...');

        $accessToken = null;
        try {
            // Using the system administrator credentials defined in DatabaseSeeder
            $authData = $authService->login('rajabannisa.wahyuni@garudapratama.com', 'gdps2019!');
            $accessToken = $authData['accessToken'] ?? $authData['access_token'] ?? null;
            $this->command->info('SSO login successful.');
        } catch (\Exception $e) {
            $this->command->error('SSO login failed: '.$e->getMessage());
            $this->command->warn('Proceeding without SSO token (will likely use fallback).');
        }

        $this->command->info('Syncing units from external API...');

        $synced = $unitService->syncFromApi($accessToken);

        if ($synced->isEmpty()) {
            $this->command->warn('No units found from API. Creating fallback unit.');

            $fallback = \Modules\MasterData\Models\Unit::updateOrCreate(
                ['code' => 'INT'],
                [
                    'name' => 'Internal Unit',
                    'external_id' => '0',
                ]
            );

            $synced->push($fallback);
        }

        $this->command->info("Successfully synced {$synced->count()} units.");
    }
}
