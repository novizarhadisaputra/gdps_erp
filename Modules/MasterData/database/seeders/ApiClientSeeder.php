<?php

namespace Modules\MasterData\Database\Seeders;

use App\Models\ApiClient;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ApiClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApiClient::updateOrCreate(
            ['name' => 'RR System'],
            [
                'client_id' => 'client_'.Str::random(16),
                'client_secret' => Str::random(32),
                'is_active' => true,
            ]
        );
    }
}
