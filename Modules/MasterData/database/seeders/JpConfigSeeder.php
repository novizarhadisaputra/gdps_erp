<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\JpConfig;

class JpConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JpConfig::updateOrCreate(
            ['name' => 'JP PPU'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.02,
                'employee_rate' => 0.01,
                'cap_nominal' => 10547400, // standard JP cap 2024
            ]
        );
    }
}
