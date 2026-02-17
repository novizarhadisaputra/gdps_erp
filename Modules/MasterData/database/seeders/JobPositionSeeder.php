<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;

class JobPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            ['name' => 'Transport Allowance', 'type' => 'fixed_allowance', 'default_amount' => 0],
            ['name' => 'Meal Allowance', 'type' => 'fixed_allowance', 'default_amount' => 0],
            ['name' => 'Service Allowance', 'type' => 'non_fixed_allowance', 'default_amount' => 0],
        ];

        foreach ($components as $c) {
            \Modules\MasterData\Models\RemunerationComponent::updateOrCreate(
                ['name' => $c['name']],
                $c
            );
        }

        $transport = \Modules\MasterData\Models\RemunerationComponent::where('name', 'Transport Allowance')->first();
        $meal = \Modules\MasterData\Models\RemunerationComponent::where('name', 'Meal Allowance')->first();

        $positions = [
            ['name' => 'Security', 'salary' => 3500000, 'risk' => 'low'],
            ['name' => 'Driver', 'salary' => 3800000, 'risk' => 'low'],
            ['name' => 'SPG', 'salary' => 3200000, 'risk' => 'very_low'],
            ['name' => 'Merchandizer', 'salary' => 3200000, 'risk' => 'very_low'],
            ['name' => 'Cleaner', 'salary' => 3000000, 'risk' => 'low'],
            ['name' => 'Engineer', 'salary' => 5500000, 'risk' => 'medium'],
            ['name' => 'Office Boy', 'salary' => 3000000, 'risk' => 'very_low'],
            ['name' => 'Receptionist', 'salary' => 3500000, 'risk' => 'very_low'],
        ];

        foreach ($positions as $p) {
            \Modules\MasterData\Models\JobPosition::updateOrCreate(
                ['name' => $p['name']],
                [
                    'basic_salary' => $p['salary'],
                    'risk_level' => $p['risk'],
                    'is_labor_intensive' => true,
                ]
            );
        }
    }
}
