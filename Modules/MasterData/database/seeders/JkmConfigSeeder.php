<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\JkmConfig;

class JkmConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JkmConfig::updateOrCreate(
            ['name' => 'JKM PPU'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.003,
                'employee_rate' => 0,
                'employer_nominal' => 0,
                'employee_nominal' => 0,
            ]
        );

        JkmConfig::updateOrCreate(
            ['name' => 'JKM PBPU / Mandiri'],
            [
                'employee_type' => 'pbpu',
                'employer_rate' => 0,
                'employee_rate' => 0,
                'employer_nominal' => 0,
                'employee_nominal' => 6800,
            ]
        );
    }
}
