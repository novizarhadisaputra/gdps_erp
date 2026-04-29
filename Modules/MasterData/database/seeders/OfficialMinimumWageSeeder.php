<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Enums\MinimumWageType;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Province;

class OfficialMinimumWageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = 2026;

        // Using Official Province Codes (BPS/Kemendagri) for absolute consistency
        $umpData = [
            '11' => 3932552.00, // Aceh
            '12' => 3228949.00, // Sumatera Utara
            '13' => 3182955.00, // Sumatera Barat
            '14' => 3780495.00, // Riau
            '15' => 3471497.00, // Jambi
            '16' => 3942963.00, // Sumatera Selatan
            '17' => 2827250.00, // Bengkulu
            '18' => 3047734.00, // Lampung
            '19' => 4035000.00, // Kepulauan Bangka Belitung
            '21' => 3879520.00, // Kepulauan Riau
            '31' => 5729876.00, // DKI Jakarta
            '32' => 2317601.00, // Jawa Barat
            '33' => 2327386.07, // Jawa Tengah
            '34' => 2417495.00, // Daerah Istimewa Yogyakarta
            '35' => 2446880.00, // Jawa Timur
            '36' => 3100881.40, // Banten
            '51' => 3207459.00, // Bali
            '52' => 2673861.00, // Nusa Tenggara Barat
            '53' => 2455898.00, // Nusa Tenggara NTT
            '61' => 3054552.00, // Kalimantan Barat
            '62' => 3686138.00, // Kalimantan Tengah
            '63' => 3725000.00, // Kalimantan Selatan
            '64' => 3762431.00, // Kalimantan Timur
            '65' => 3775243.00, // Kalimantan Utara
            '71' => 4002630.00, // Sulawesi Utara
            '72' => 3179565.00, // Sulawesi Tengah
            '73' => 3921088.00, // Sulawesi Selatan
            '74' => 3306496.18, // Sulawesi Tenggara
            '75' => 3405144.00, // Gorontalo
            '76' => 3315934.00, // Sulawesi Barat
            '81' => 3334490.00, // Maluku
            '82' => 3510240.00, // Maluku Utara
            '91' => 4436283.00, // Papua
            '92' => 3841000.00, // Papua Barat
            '93' => 4508100.00, // Papua Selatan
            '94' => 4285848.00, // Papua Tengah
            '95' => 4508714.00, // Papua Pegunungan
            '96' => 3766000.00, // Papua Barat Daya
        ];

        foreach ($umpData as $provinceCode => $amount) {
            // 1. Validate against the provinces table
            $province = Province::where('code', $provinceCode)->first();

            if (! $province) {
                $this->command->warn("Province with code {$provinceCode} not found in database. Skipping...");

                continue;
            }

            // 2. Find the corresponding root ProjectArea for this province
            $provinceArea = ProjectArea::where('province_id', $province->id)
                ->whereNull('regency_id')
                ->first();

            if ($provinceArea) {
                // Upsert Provincial Level UMP
                MinimumWage::updateOrCreate(
                    ['project_area_id' => $provinceArea->id, 'year' => $year],
                    [
                        'amount' => $amount,
                        'province' => $province->name,
                        'type' => MinimumWageType::Province,
                        'is_active' => true,
                    ]
                );
            }

            // 3. Propagate to all Regencies/Cities within this province (regardless of root area existence)
            $childAreas = ProjectArea::where('province_id', $province->id)
                ->whereNotNull('regency_id')
                ->get();

            if ($childAreas->count() > 0) {
                $this->command->info("Applying UMP to {$childAreas->count()} regencies in {$province->name}");

                foreach ($childAreas as $area) {
                    $type = str_contains(strtolower($area->name), 'kabupaten')
                        ? MinimumWageType::Regency
                        : MinimumWageType::City;

                    MinimumWage::updateOrCreate(
                        ['project_area_id' => $area->id, 'year' => $year],
                        [
                            'amount' => $amount,
                            'province' => $province->name,
                            'type' => $type,
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        $this->command->info('Official Minimum Wage seeding completed consistently.');
    }
}
