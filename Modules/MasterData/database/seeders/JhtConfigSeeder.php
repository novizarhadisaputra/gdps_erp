<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\JhtConfig;

class JhtConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        JhtConfig::updateOrCreate(
            ['name' => 'JHT PPU'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.037,
                'employee_rate' => 0.02,
                'has_tier' => false,
            ]
        );

        $jhtBpu = JhtConfig::updateOrCreate(
            ['name' => 'JHT PBPU / Mandiri'],
            [
                'employee_type' => 'pbpu',
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
            ]
        );

        if ($jhtBpu->tiers()->count() === 0) {
            $jhtBpu->tiers()->createMany([
                ['min_income' => 0, 'max_income' => null, 'employer_nominal' => 0, 'employee_nominal' => 41400],
            ]);
        }
    }
}
