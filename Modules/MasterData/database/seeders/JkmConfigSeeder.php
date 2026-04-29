<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJkmConfig;

class JkmConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BpjsJkmConfig::updateOrCreate(
            ['name' => 'JKM PPU'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.003,
                'employee_rate' => 0,
                'employer_nominal' => 0,
                'employee_nominal' => 0,
                'is_default' => true,
            ]
        );

        BpjsJkmConfig::updateOrCreate(
            ['name' => 'JKM PBPU / Mandiri'],
            [
                'employee_type' => 'pbpu',
                'employer_rate' => 0,
                'employee_rate' => 0,
                'employer_nominal' => 0,
                'employee_nominal' => 6800,
                'is_default' => false,
            ]
        );

        // Jakon (Construction) configuration
        $jkmJakon = BpjsJkmConfig::updateOrCreate(
            ['name' => 'JKM Jakon (Konstruksi)'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
                'is_default' => false,
            ]
        );

        if ($jkmJakon->tiers()->count() === 0) {
            $jkmJakon->tiers()->createMany([
                ['min_value' => 0, 'max_value' => 100000000, 'employer_rate' => 0.0003],
                ['min_value' => 100000001, 'max_value' => 500000000, 'employer_rate' => 0.0002],
                ['min_value' => 500000001, 'max_value' => 1000000000, 'employer_rate' => 0.0002],
                ['min_value' => 1000000001, 'max_value' => 5000000000, 'employer_rate' => 0.0001],
                ['min_value' => 5000000001, 'max_value' => null, 'employer_rate' => 0.0001],
            ]);
        }
    }
}
