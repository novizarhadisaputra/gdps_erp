<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\BpjsJpConfig;

class JpConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Tipe 1: 2% Perusahaan, 1% Peserta
        BpjsJpConfig::updateOrCreate(
            ['name' => 'Tipe 1: 2% Perusahaan, 1% Peserta'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.02,
                'employee_rate' => 0.01,
                'cap_nominal' => 11086300, // standard JP cap updated to match COSTING MP R1
                'is_default' => true,
            ]
        );

        // 2. Tipe 2: Tidak Ada Iuran
        BpjsJpConfig::updateOrCreate(
            ['name' => 'Tipe 2: Tidak Ada Iuran'],
            [
                'employee_type' => 'ppu',
                'employer_rate' => 0.0,
                'employee_rate' => 0.0,
                'cap_nominal' => 0,
                'is_default' => false,
            ]
        );

        // Clean up legacy JP PPU name if present
        BpjsJpConfig::where('name', 'JP PPU')->delete();
    }
}
