<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsTier;

class JkkConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up existing records to prevent duplicates and orphans with obsolete names
        BpjsJkkConfig::query()->delete();

        $configs = [
            ['name' => '0.24% Perusahaan (Sangat Rendah)', 'employee_type' => 'ppu', 'risk_level' => 'very_low', 'employer_rate' => 0.0024, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => true],
            ['name' => '0.54% Perusahaan (Rendah)', 'employee_type' => 'ppu', 'risk_level' => 'low', 'employer_rate' => 0.0054, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => '0.89% Perusahaan (Sedang)', 'employee_type' => 'ppu', 'risk_level' => 'medium', 'employer_rate' => 0.0089, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => '1.27% Perusahaan (Tinggi)', 'employee_type' => 'ppu', 'risk_level' => 'high', 'employer_rate' => 0.0127, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => '1.74% Perusahaan (Sangat Tinggi)', 'employee_type' => 'ppu', 'risk_level' => 'very_high', 'employer_rate' => 0.0174, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
            ['name' => 'Tidak Ada Iuran', 'employee_type' => 'ppu', 'risk_level' => null, 'employer_rate' => 0.0, 'employee_rate' => 0, 'has_tier' => false, 'is_default' => false],
        ];

        foreach ($configs as $config) {
            BpjsJkkConfig::create($config);
        }

        // BPU/PBPU configuration with Tiers (Lookup Tier Peserta)
        BpjsJkkConfig::create([
            'name' => 'Lookup Tier Peserta',
            'employee_type' => 'pbpu',
            'risk_level' => null,
            'employer_rate' => 0,
            'employee_rate' => 0,
            'has_tier' => true,
            'tier_category' => 'jkk_bpu',
            'is_default' => false,
        ]);

        BpjsTier::where('category', 'jkk_bpu')->delete();
        $bpuTiers = [
            ['category' => 'jkk_bpu', 'min_value' => 0, 'max_value' => 1000000, 'employer_nominal' => 0, 'employee_nominal' => 10000],
            ['category' => 'jkk_bpu', 'min_value' => 1000001, 'max_value' => 2000000, 'employer_nominal' => 0, 'employee_nominal' => 20000],
            ['category' => 'jkk_bpu', 'min_value' => 2000001, 'max_value' => null, 'employer_nominal' => 0, 'employee_nominal' => 30000],
        ];

        foreach ($bpuTiers as $tier) {
            BpjsTier::create($tier);
        }

        // Jakon (Construction) configuration (Lookup Kontrak Perusahaan)
        BpjsJkkConfig::create([
            'name' => 'Lookup Kontrak Perusahaan',
            'employee_type' => 'ppu',
            'risk_level' => null,
            'employer_rate' => 0,
            'employee_rate' => 0,
            'has_tier' => true,
            'tier_category' => 'jkk_jakon',
            'is_default' => false,
        ]);

        BpjsTier::where('category', 'jkk_jakon')->delete();
        $jakonTiers = [
            ['category' => 'jkk_jakon', 'min_value' => 0, 'max_value' => 100000000, 'employer_rate' => 0.0021],
            ['category' => 'jkk_jakon', 'min_value' => 100000001, 'max_value' => 500000000, 'employer_rate' => 0.0017],
            ['category' => 'jkk_jakon', 'min_value' => 500000001, 'max_value' => 1000000000, 'employer_rate' => 0.0013],
            ['category' => 'jkk_jakon', 'min_value' => 1000000001, 'max_value' => 5000000000, 'employer_rate' => 0.0011],
            ['category' => 'jkk_jakon', 'min_value' => 5000000001, 'max_value' => null, 'employer_rate' => 0.0009],
        ];

        foreach ($jakonTiers as $tier) {
            BpjsTier::create($tier);
        }
    }
}
