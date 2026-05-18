<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsTier;

class JkmConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up existing records to prevent duplicates and orphans with obsolete names
        BpjsJkmConfig::query()->delete();

        BpjsJkmConfig::create([
            'name' => '0.30% Perusahaan',
            'employee_type' => 'ppu',
            'employer_rate' => 0.003,
            'employee_rate' => 0,
            'employer_nominal' => 0,
            'employee_nominal' => 0,
            'is_default' => true,
        ]);

        BpjsJkmConfig::create([
            'name' => 'Lookup Tier Peserta',
            'employee_type' => 'pbpu',
            'employer_rate' => 0,
            'employee_rate' => 0,
            'employer_nominal' => 0,
            'employee_nominal' => 6800,
            'is_default' => false,
        ]);

        // Jakon (Construction) configuration (Lookup Kontrak Perusahaan)
        BpjsJkmConfig::create([
            'name' => 'Lookup Kontrak Perusahaan',
            'employee_type' => 'ppu',
            'employer_rate' => 0,
            'employee_rate' => 0,
            'has_tier' => true,
            'tier_category' => 'jkm_jakon',
            'is_default' => false,
        ]);

        BpjsTier::where('category', 'jkm_jakon')->delete();
        $tiers = [
            ['category' => 'jkm_jakon', 'min_value' => 0, 'max_value' => 100000000, 'employer_rate' => 0.0003],
            ['category' => 'jkm_jakon', 'min_value' => 100000001, 'max_value' => 500000000, 'employer_rate' => 0.0002],
            ['category' => 'jkm_jakon', 'min_value' => 500000001, 'max_value' => 1000000000, 'employer_rate' => 0.0002],
            ['category' => 'jkm_jakon', 'min_value' => 1000000001, 'max_value' => 5000000000, 'employer_rate' => 0.0001],
            ['category' => 'jkm_jakon', 'min_value' => 5000000001, 'max_value' => null, 'employer_rate' => 0.0001],
        ];

        foreach ($tiers as $tier) {
            BpjsTier::create($tier);
        }
    }
}
