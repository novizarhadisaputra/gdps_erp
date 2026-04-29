<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJkkConfig;

class JkkConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            ['name' => 'JKK PPU - Very Low', 'employee_type' => 'ppu', 'risk_level' => 'very_low', 'employer_rate' => 0.0024, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => true],
            ['name' => 'JKK PPU - Low', 'employee_type' => 'ppu', 'risk_level' => 'low', 'employer_rate' => 0.0054, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => 'JKK PPU - Medium', 'employee_type' => 'ppu', 'risk_level' => 'medium', 'employer_rate' => 0.0089, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => 'JKK PPU - High', 'employee_type' => 'ppu', 'risk_level' => 'high', 'employer_rate' => 0.0127, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => 'JKK PPU - Very High', 'employee_type' => 'ppu', 'risk_level' => 'very_high', 'employer_rate' => 0.0174, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
        ];

        foreach ($configs as $config) {
            BpjsJkkConfig::updateOrCreate(['name' => $config['name']], $config);
        }

        // BPU/PBPU configuration with Tiers
        $jkkBpu = BpjsJkkConfig::updateOrCreate(
            ['name' => 'JKK BPU / Mandiri (Tiered)'],
            [
                'employee_type' => 'pbpu',
                'risk_level' => null,
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
                'is_default' => false,
            ]
        );

        if ($jkkBpu->tiers()->count() === 0) {
            $jkkBpu->tiers()->createMany([
                ['min_value' => 0, 'max_value' => 1000000, 'employer_nominal' => 0, 'employee_nominal' => 10000],
                ['min_value' => 1000001, 'max_value' => 2000000, 'employer_nominal' => 0, 'employee_nominal' => 20000],
                ['min_value' => 2000001, 'max_value' => null, 'employer_nominal' => 0, 'employee_nominal' => 30000],
            ]);
        }

        // Jakon (Construction) configuration
        $jkkJakon = BpjsJkkConfig::updateOrCreate(
            ['name' => 'JKK Jakon (Konstruksi)'],
            [
                'employee_type' => 'ppu',
                'risk_level' => null,
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
                'is_default' => false,
            ]
        );

        if ($jkkJakon->tiers()->count() === 0) {
            $jkkJakon->tiers()->createMany([
                ['min_value' => 0, 'max_value' => 100000000, 'employer_rate' => 0.0021],
                ['min_value' => 100000001, 'max_value' => 500000000, 'employer_rate' => 0.0017],
                ['min_value' => 500000001, 'max_value' => 1000000000, 'employer_rate' => 0.0013],
                ['min_value' => 1000000001, 'max_value' => 5000000000, 'employer_rate' => 0.0011],
                ['min_value' => 5000000001, 'max_value' => null, 'employer_rate' => 0.0009],
            ]);
        }
    }
}
