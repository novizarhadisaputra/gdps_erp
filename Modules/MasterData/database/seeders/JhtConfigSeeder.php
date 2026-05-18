<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsTier;

class JhtConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BpjsJhtConfig::updateOrCreate(
            ['name' => 'JHT PPU'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.037,
                'employee_rate' => 0.02,
                'has_tier' => false,
                'is_default' => true,
            ]
        );

        BpjsJhtConfig::updateOrCreate(
            ['name' => 'JHT PBPU / Mandiri'],
            [
                'employee_type' => 'pbpu',
                'employer_rate' => 0,
                'employee_rate' => 0,
                'has_tier' => true,
                'tier_category' => 'jht_pbpu',
                'is_default' => false,
            ]
        );

        BpjsTier::where('category', 'jht_pbpu')->delete();
        $tiers = [
            ['category' => 'jht_pbpu', 'min_value' => 0, 'max_value' => 1099999, 'employer_nominal' => 0, 'employee_nominal' => 20000],
            ['category' => 'jht_pbpu', 'min_value' => 1100000, 'max_value' => 1299999, 'employer_nominal' => 0, 'employee_nominal' => 24000],
            ['category' => 'jht_pbpu', 'min_value' => 1300000, 'max_value' => 1499999, 'employer_nominal' => 0, 'employee_nominal' => 28000],
            ['category' => 'jht_pbpu', 'min_value' => 1500000, 'max_value' => 1699999, 'employer_nominal' => 0, 'employee_nominal' => 32000],
            ['category' => 'jht_pbpu', 'min_value' => 1700000, 'max_value' => 1899999, 'employer_nominal' => 0, 'employee_nominal' => 36000],
            ['category' => 'jht_pbpu', 'min_value' => 1900000, 'max_value' => 2099999, 'employer_nominal' => 0, 'employee_nominal' => 40000],
            ['category' => 'jht_pbpu', 'min_value' => 2100000, 'max_value' => 2299999, 'employer_nominal' => 0, 'employee_nominal' => 44000],
            ['category' => 'jht_pbpu', 'min_value' => 2300000, 'max_value' => 2499999, 'employer_nominal' => 0, 'employee_nominal' => 48000],
            ['category' => 'jht_pbpu', 'min_value' => 2500000, 'max_value' => 2699999, 'employer_nominal' => 0, 'employee_nominal' => 52000],
            ['category' => 'jht_pbpu', 'min_value' => 2700000, 'max_value' => 3199999, 'employer_nominal' => 0, 'employee_nominal' => 59000],
            ['category' => 'jht_pbpu', 'min_value' => 3200000, 'max_value' => 3699999, 'employer_nominal' => 0, 'employee_nominal' => 69000],
            ['category' => 'jht_pbpu', 'min_value' => 3700000, 'max_value' => 4199999, 'employer_nominal' => 0, 'employee_nominal' => 79000],
            ['category' => 'jht_pbpu', 'min_value' => 4200000, 'max_value' => 4699999, 'employer_nominal' => 0, 'employee_nominal' => 89000],
            ['category' => 'jht_pbpu', 'min_value' => 4700000, 'max_value' => 5199999, 'employer_nominal' => 0, 'employee_nominal' => 99000],
            ['category' => 'jht_pbpu', 'min_value' => 5200000, 'max_value' => 5699999, 'employer_nominal' => 0, 'employee_nominal' => 109000],
            ['category' => 'jht_pbpu', 'min_value' => 5700000, 'max_value' => 6199999, 'employer_nominal' => 0, 'employee_nominal' => 119000],
            ['category' => 'jht_pbpu', 'min_value' => 6200000, 'max_value' => 6699999, 'employer_nominal' => 0, 'employee_nominal' => 129000],
            ['category' => 'jht_pbpu', 'min_value' => 6700000, 'max_value' => 7199999, 'employer_nominal' => 0, 'employee_nominal' => 139000],
            ['category' => 'jht_pbpu', 'min_value' => 7200000, 'max_value' => 7699999, 'employer_nominal' => 0, 'employee_nominal' => 149000],
            ['category' => 'jht_pbpu', 'min_value' => 7700000, 'max_value' => 8199999, 'employer_nominal' => 0, 'employee_nominal' => 159000],
            ['category' => 'jht_pbpu', 'min_value' => 8200000, 'max_value' => 9199999, 'employer_nominal' => 0, 'employee_nominal' => 174000],
            ['category' => 'jht_pbpu', 'min_value' => 9200000, 'max_value' => 10199999, 'employer_nominal' => 0, 'employee_nominal' => 194000],
            ['category' => 'jht_pbpu', 'min_value' => 10200000, 'max_value' => 11199999, 'employer_nominal' => 0, 'employee_nominal' => 214000],
            ['category' => 'jht_pbpu', 'min_value' => 11200000, 'max_value' => 12199999, 'employer_nominal' => 0, 'employee_nominal' => 234000],
            ['category' => 'jht_pbpu', 'min_value' => 12200000, 'max_value' => 13199999, 'employer_nominal' => 0, 'employee_nominal' => 254000],
            ['category' => 'jht_pbpu', 'min_value' => 13200000, 'max_value' => 14199999, 'employer_nominal' => 0, 'employee_nominal' => 274000],
            ['category' => 'jht_pbpu', 'min_value' => 14200000, 'max_value' => 15199999, 'employer_nominal' => 0, 'employee_nominal' => 294000],
            ['category' => 'jht_pbpu', 'min_value' => 15200000, 'max_value' => 16199999, 'employer_nominal' => 0, 'employee_nominal' => 314000],
            ['category' => 'jht_pbpu', 'min_value' => 16200000, 'max_value' => 17199999, 'employer_nominal' => 0, 'employee_nominal' => 334000],
            ['category' => 'jht_pbpu', 'min_value' => 17200000, 'max_value' => 18199999, 'employer_nominal' => 0, 'employee_nominal' => 354000],
            ['category' => 'jht_pbpu', 'min_value' => 18200000, 'max_value' => 19199999, 'employer_nominal' => 0, 'employee_nominal' => 374000],
            ['category' => 'jht_pbpu', 'min_value' => 19200000, 'max_value' => 20199999, 'employer_nominal' => 0, 'employee_nominal' => 394000],
            ['category' => 'jht_pbpu', 'min_value' => 20200000, 'max_value' => null, 'employer_nominal' => 0, 'employee_nominal' => 414000],
        ];

        foreach ($tiers as $tier) {
            BpjsTier::create($tier);
        }
    }
}
