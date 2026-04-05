<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\JkkConfig;

class JkkConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            ['name' => 'JKK PPU - Very Low', 'employee_type' => 'ppu', 'risk_level' => 'very_low', 'employer_rate' => 0.0024, 'employee_rate' => 0, 'has_tier' => false],
            ['name' => 'JKK PPU - Low', 'employee_type' => 'ppu', 'risk_level' => 'low', 'employer_rate' => 0.0054, 'employee_rate' => 0, 'has_tier' => false],
            ['name' => 'JKK PPU - Medium', 'employee_type' => 'ppu', 'risk_level' => 'medium', 'employer_rate' => 0.0089, 'employee_rate' => 0, 'has_tier' => false],
            ['name' => 'JKK PPU - High', 'employee_type' => 'ppu', 'risk_level' => 'high', 'employer_rate' => 0.0127, 'employee_rate' => 0, 'has_tier' => false],
            ['name' => 'JKK PPU - Very High', 'employee_type' => 'ppu', 'risk_level' => 'very_high', 'employer_rate' => 0.0174, 'employee_rate' => 0, 'has_tier' => false],
        ];

        foreach ($configs as $config) {
            JkkConfig::updateOrCreate(['name' => $config['name']], $config);
        }

        // BPU/PBPU configuration with Tiers
        $jkkBpu = JkkConfig::updateOrCreate(
            ['name' => 'JKK BPU / Mandiri (Tiered)'],
            [
                'employee_type' => 'pbpu',
                'risk_level' => null,
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
            ]
        );

        if ($jkkBpu->tiers()->count() === 0) {
            $jkkBpu->tiers()->createMany([
                ['min_income' => 0, 'max_income' => 1000000, 'employer_nominal' => 0, 'employee_nominal' => 10000],
                ['min_income' => 1000001, 'max_income' => 2000000, 'employer_nominal' => 0, 'employee_nominal' => 20000],
                ['min_income' => 2000001, 'max_income' => null, 'employer_nominal' => 0, 'employee_nominal' => 30000],
            ]);
        }
    }
}
