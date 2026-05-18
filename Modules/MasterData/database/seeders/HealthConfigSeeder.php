<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsHealthConfig;

class HealthConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clean up old config names to ensure consistency
        BpjsHealthConfig::query()->delete();

        // 1. Tipe 1: 4% Perusahaan, 1% Peserta (Min. UMK, Max. 12jt)
        BpjsHealthConfig::create([
            'name' => 'Tipe 1: 4% Perusahaan, 1% Peserta (Min. UMK, Max. 12jt)',
            'employee_type' => 'ppu',
            'floor_type' => 'umk',
            'employer_rate' => 0.04,
            'employee_rate' => 0.01,
            'cap_nominal' => 12000000,
            'is_default' => true,
            'is_active' => true,
        ]);

        // 2. Tipe 2: Tidak Ada Iuran
        BpjsHealthConfig::create([
            'name' => 'Tipe 2: Tidak Ada Iuran',
            'employee_type' => 'ppu',
            'floor_type' => 'nominal',
            'employer_rate' => 0.0,
            'employee_rate' => 0.0,
            'employer_nominal' => 0,
            'employee_nominal' => 0,
            'cap_nominal' => 0,
            'is_default' => false,
            'is_active' => true,
        ]);

        BpjsHealthConfig::create([
            'name' => 'Kelas I',
            'employee_type' => 'pbpu',
            'floor_type' => 'nominal',
            'employer_nominal' => 0,
            'employee_nominal' => 150000,
            'is_default' => false,
            'is_active' => true,
        ]);

        BpjsHealthConfig::create([
            'name' => 'Kelas II',
            'employee_type' => 'pbpu',
            'floor_type' => 'nominal',
            'employer_nominal' => 0,
            'employee_nominal' => 100000,
            'is_default' => false,
            'is_active' => true,
        ]);

        BpjsHealthConfig::create([
            'name' => 'Kelas III',
            'employee_type' => 'pbpu',
            'floor_type' => 'nominal',
            'employer_nominal' => 0,
            'employee_nominal' => 35000,
            'is_default' => false,
            'is_active' => true,
        ]);

        BpjsHealthConfig::create([
            'name' => 'Penerima Bantuan Iuran (PBI)',
            'employee_type' => 'pbi',
            'floor_type' => 'nominal',
            'employer_nominal' => 0,
            'employee_nominal' => 0,
            'is_default' => false,
            'is_active' => true,
        ]);
    }
}
