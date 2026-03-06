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
            ['name' => 'Security', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['name' => 'Driver', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['name' => 'SPG', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['name' => 'Merchandizer', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['name' => 'Cleaner', 'risk_level' => 'low', 'is_labor_intensive' => true],
            ['name' => 'Engineer', 'risk_level' => 'medium', 'is_labor_intensive' => false],
            ['name' => 'Office Boy', 'risk_level' => 'very_low', 'is_labor_intensive' => true],
            ['name' => 'Receptionist', 'risk_level' => 'very_low', 'is_labor_intensive' => false],
        ];

        foreach ($positions as $p) {
            $jobPosition = JobPosition::updateOrCreate(
                ['name' => $p['name']],
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
