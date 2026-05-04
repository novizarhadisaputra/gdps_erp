<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\FixedAllowance;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\NonFixedAllowance;

class JobPositionSeeder extends Seeder
{
    public function run(): void
    {
        $fixedAllowances = FixedAllowance::whereIn('name', ['Tunjangan Jabatan', 'Tunjangan Fungsional'])->get();
        $nonFixedAllowances = NonFixedAllowance::whereIn('name', ['Tunjangan Makan (Tidak Tetap)', 'Tunjangan Transportasi (Tidak Tetap)'])->get();

        $positions = [
            ['code' => 'MGR_SEC', 'name' => 'Manager Security', 'risk_level' => 'low', 'is_labor_intensive' => false],
            ['code' => 'CHF_SEC', 'name' => 'Chief Security', 'risk_level' => 'low', 'is_labor_intensive' => false],
            ['code' => 'DNR', 'name' => 'Danru (Komandan Regu)', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['code' => 'SEC', 'name' => 'Security', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['code' => 'ADM_SEC', 'name' => 'Admin Security', 'risk_level' => 'low', 'is_labor_intensive' => false],
            ['code' => 'RCP', 'name' => 'Receptionist', 'risk_level' => 'very_low', 'is_labor_intensive' => false],
            ['code' => 'BTL', 'name' => 'Butler', 'risk_level' => 'very_low', 'is_labor_intensive' => false],
            ['code' => 'DMN', 'name' => 'Doorman', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['code' => 'CLN', 'name' => 'Cleaner', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['code' => 'OBY', 'name' => 'Office Boy', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['code' => 'SPG', 'name' => 'SPG', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['code' => 'MCD', 'name' => 'Merchandizer', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['code' => 'DRV', 'name' => 'Driver', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['code' => 'ENG', 'name' => 'Engineer', 'risk_level' => 'medium', 'is_labor_intensive' => false],
        ];

        foreach ($positions as $p) {
            $jobPosition = JobPosition::updateOrCreate(
                ['code' => $p['code']],
                $p
            );

            // Seed some default allowances
            if ($fixedAllowances->isNotEmpty()) {
                $jobPosition->fixedAllowances()->updateOrCreate(
                    ['fixed_allowance_id' => $fixedAllowances->first()->id],
                    ['amount' => 500000]
                );
            }

            if ($nonFixedAllowances->isNotEmpty()) {
                $jobPosition->nonFixedAllowances()->updateOrCreate(
                    ['non_fixed_allowance_id' => $nonFixedAllowances->first()->id],
                    ['amount' => 20000] // e.g. per day
                );
            }
        }
    }
}
