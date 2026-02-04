<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;

class RegencyMinimumWageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jakarta = \Modules\MasterData\Models\ProjectArea::where('name', 'Jakarta')->first();
        $tangerang = \Modules\MasterData\Models\ProjectArea::where('name', 'Tangerang')->first();

        if ($jakarta) {
            \Modules\MasterData\Models\RegencyMinimumWage::updateOrCreate(
                ['project_area_id' => $jakarta->id, 'year' => 2025],
                ['amount' => 5067381]
            );
        }

        if ($tangerang) {
            \Modules\MasterData\Models\RegencyMinimumWage::updateOrCreate(
                ['project_area_id' => $tangerang->id, 'year' => 2025],
                ['amount' => 4910000]
            );
        }
    }
}
