<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Enums\RegencyMinimumWageType;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\RegencyMinimumWage;

class RegencyMinimumWageSeeder extends Seeder
{
    public function run(): void
    {
        $year = 2026;

        $umkData = [
            // DKI Jakarta
            ['Jakarta', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],
            ['Jakarta Pusat', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],
            ['Jakarta Utara', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],
            ['Jakarta Barat', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],
            ['Jakarta Selatan', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],
            ['Jakarta Timur', 5067381, 'DKI Jakarta', RegencyMinimumWageType::City],

            // Jawa Barat
            ['Bekasi', 5343430, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Karawang', 5257834, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Depok', 4978273, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Bogor', 4813988, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Purwakarta', 4500000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Bandung', 4027188, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Bandung Barat', 3500000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Cimahi', 3500000, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Cianjur', 2800000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Sukabumi', 3500000, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Indramayu', 2600000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Cirebon', 2500000, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Majalengka', 2200000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Kuningan', 2100000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Sumedang', 3100000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Garut', 2100000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Tasikmalaya', 2600000, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Ciamis', 2000000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Banjar', 2000000, 'Jawa Barat', RegencyMinimumWageType::City],
            ['Pangandaran', 2000000, 'Jawa Barat', RegencyMinimumWageType::Regency],
            ['Subang', 3200000, 'Jawa Barat', RegencyMinimumWageType::Regency],

            // Banten
            ['Tangerang', 4910000, 'Banten', RegencyMinimumWageType::City],
            ['Tangerang Selatan', 4670791, 'Banten', RegencyMinimumWageType::City],
            ['Cilegon', 4813988, 'Banten', RegencyMinimumWageType::City],
            ['Serang', 4500000, 'Banten', RegencyMinimumWageType::City],
            ['Pandeglang', 3000000, 'Banten', RegencyMinimumWageType::Regency],
            ['Lebak', 3000000, 'Banten', RegencyMinimumWageType::Regency],

            // Jawa Timur
            ['Surabaya', 4725000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Gresik', 4522030, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Sidoarjo', 4547000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Pasuruan', 4515000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Mojokerto', 4500000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Malang', 3300000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Batu', 3100000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Jombang', 3000000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Probolinggo', 2700000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Tuban', 2700000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Banyuwangi', 2600000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Lamongan', 2800000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Jember', 2500000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Kediri', 2300000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Madiun', 2200000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Blitar', 2200000, 'Jawa Timur', RegencyMinimumWageType::City],
            ['Tulungagung', 2200000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Nganjuk', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Bojonegoro', 2300000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Bondowoso', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Situbondo', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Lumajang', 2200000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Magetan', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Ponorogo', 2200000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Trenggalek', 2000000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Pacitan', 2000000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Sumenep', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Pamekasan', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Sampang', 2100000, 'Jawa Timur', RegencyMinimumWageType::Regency],
            ['Bangkalan', 2200000, 'Jawa Timur', RegencyMinimumWageType::Regency],

            // Bali & Nusa Tenggara
            ['Denpasar', 3100000, 'Bali', RegencyMinimumWageType::City],
            ['Badung', 3400000, 'Bali', RegencyMinimumWageType::Regency],
            ['Gianyar', 2900000, 'Bali', RegencyMinimumWageType::Regency],
            ['Mataram', 2600000, 'Nusa Tenggara Barat', RegencyMinimumWageType::City],
            ['Kupang', 2200000, 'Nusa Tenggara Timur', RegencyMinimumWageType::City],

            // Sumatera
            ['Banda Aceh', 3500000, 'Aceh', RegencyMinimumWageType::City],
            ['Medan', 3769000, 'Sumatera Utara', RegencyMinimumWageType::City],
            ['Padang', 2811449, 'Sumatera Barat', RegencyMinimumWageType::City],
            ['Pekanbaru', 3400000, 'Riau', RegencyMinimumWageType::City],
            ['Batam', 4685000, 'Kepulauan Riau', RegencyMinimumWageType::City],
            ['Jambi', 3200000, 'Jambi', RegencyMinimumWageType::City],
            ['Palembang', 3600000, 'Sumatera Selatan', RegencyMinimumWageType::City],
            ['Bengkulu', 2800000, 'Bengkulu', RegencyMinimumWageType::City],
            ['Bandar Lampung', 3100000, 'Lampung', RegencyMinimumWageType::City],
            ['Pangkal Pinang', 3000000, 'Kepulauan Bangka Belitung', RegencyMinimumWageType::City],

            // Kalimantan
            ['Banjarmasin', 3614138, 'Kalimantan Selatan', RegencyMinimumWageType::City],
            ['Balikpapan', 3474000, 'Kalimantan Timur', RegencyMinimumWageType::City],
            ['Samarinda', 3497000, 'Kalimantan Timur', RegencyMinimumWageType::City],
            ['Pontianak', 3054552, 'Kalimantan Barat', RegencyMinimumWageType::City],
            ['Palangka Raya', 3631980, 'Kalimantan Tengah', RegencyMinimumWageType::City],
            ['Tarakan', 3692484, 'Kalimantan Utara', RegencyMinimumWageType::City],

            // Sulawesi
            ['Manado', 3835698, 'Sulawesi Utara', RegencyMinimumWageType::City],
            ['Makassar', 3647000, 'Sulawesi Selatan', RegencyMinimumWageType::City],
            ['Kendari', 3100000, 'Sulawesi Tenggara', RegencyMinimumWageType::City],
            ['Palu', 3015945, 'Sulawesi Tengah', RegencyMinimumWageType::City],
            ['Gorontalo', 3100000, 'Gorontalo', RegencyMinimumWageType::City],
            ['Mamuju', 3000000, 'Sulawesi Barat', RegencyMinimumWageType::Regency],

            // Maluku & Papua
            ['Ambon', 3000000, 'Maluku', RegencyMinimumWageType::City],
            ['Ternate', 3200000, 'Maluku Utara', RegencyMinimumWageType::City],
            ['Jayapura', 4000000, 'Papua', RegencyMinimumWageType::City],
            ['Sorong', 4020000, 'Papua Barat Daya', RegencyMinimumWageType::City],
            ['Manokwari', 3500000, 'Papua Barat', RegencyMinimumWageType::Regency],
            ['Merauke', 3800000, 'Papua Selatan', RegencyMinimumWageType::Regency],
            ['Mimika', 3950000, 'Papua Tengah', RegencyMinimumWageType::Regency],
        ];

        foreach ($umkData as $item) {
            $areaName = $item[0];
            $amount = $item[1];
            $province = $item[2];
            $type = $item[3];

            $area = ProjectArea::where('name', $areaName)->first();

            if ($area) {
                RegencyMinimumWage::updateOrCreate(
                    ['project_area_id' => $area->id, 'year' => $year],
                    [
                        'amount' => $amount,
                        'province' => $province,
                        'type' => $type,
                        'is_active' => true,
                    ]
                );
            } else {
                $newArea = ProjectArea::create([
                    'name' => $areaName,
                    'is_active' => true,
                ]);

                RegencyMinimumWage::create([
                    'project_area_id' => $newArea->id,
                    'year' => $year,
                    'amount' => $amount,
                    'province' => $province,
                    'type' => $type,
                    'is_active' => true,
                ]);
            }
        }
    }
}
