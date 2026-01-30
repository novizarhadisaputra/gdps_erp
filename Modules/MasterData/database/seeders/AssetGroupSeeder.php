<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Enums\AssetGroupType;
use Modules\MasterData\Models\AssetGroup;

class AssetGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groups = [
            [
                'name' => 'Kelompok 1 (Bukan Bangunan)',
                'type' => AssetGroupType::TangibleNonBuilding,
                'useful_life_years' => 4,
                'rate_straight_line' => 25.00,
                'rate_declining_balance' => 50.00,
                'description' => 'Masa manfaat 4 tahun.',
            ],
            [
                'name' => 'Kelompok 2 (Bukan Bangunan)',
                'type' => AssetGroupType::TangibleNonBuilding,
                'useful_life_years' => 8,
                'rate_straight_line' => 12.50,
                'rate_declining_balance' => 25.00,
                'description' => 'Masa manfaat 8 tahun.',
            ],
            [
                'name' => 'Kelompok 3 (Bukan Bangunan)',
                'type' => AssetGroupType::TangibleNonBuilding,
                'useful_life_years' => 16,
                'rate_straight_line' => 6.25,
                'rate_declining_balance' => 12.50,
                'description' => 'Masa manfaat 16 tahun.',
            ],
            [
                'name' => 'Kelompok 4 (Bukan Bangunan)',
                'type' => AssetGroupType::TangibleNonBuilding,
                'useful_life_years' => 20,
                'rate_straight_line' => 5.00,
                'rate_declining_balance' => 10.00,
                'description' => 'Masa manfaat 20 tahun.',
            ],
            [
                'name' => 'Bangunan Permanen',
                'type' => AssetGroupType::TangibleBuilding,
                'useful_life_years' => 20,
                'rate_straight_line' => 5.00,
                'rate_declining_balance' => null,
                'description' => 'Bangunan permanen.',
            ],
            [
                'name' => 'Bangunan Tidak Permanen',
                'type' => AssetGroupType::TangibleBuilding,
                'useful_life_years' => 10,
                'rate_straight_line' => 10.00,
                'rate_declining_balance' => null,
                'description' => 'Bangunan tidak permanen.',
            ],
        ];

        foreach ($groups as $group) {
            AssetGroup::updateOrCreate(
                ['name' => $group['name']],
                $group
            );
        }
    }
}
