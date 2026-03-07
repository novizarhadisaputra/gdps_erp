<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\HealthConfig;

class HealthConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HealthConfig::updateOrCreate(
            ['name' => 'Kesehatan PPU'],
            [
                'employee_type' => 'ppu',
                'floor_type' => 'umk', // UMK limit
                'employer_rate' => 0.04,
                'employee_rate' => 0.01,
                'cap_nominal' => 12000000,
            ]
        );

        HealthConfig::updateOrCreate(
            ['name' => 'Kesehatan PBPU / Mandiri (Kelas 1)'],
            [
                'employee_type' => 'pbpu',
                'employer_nominal' => 0,
                'employee_nominal' => 150000,
            ]
        );

        HealthConfig::updateOrCreate(
            ['name' => 'Kesehatan PBI'],
            [
                'employee_type' => 'pbi',
                'employer_nominal' => 0,
                'employee_nominal' => 0,
            ]
        );
    }
}
