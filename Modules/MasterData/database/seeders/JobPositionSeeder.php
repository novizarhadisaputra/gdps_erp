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
            ['name' => 'Transport Allowance', 'category' => 'allowance', 'is_fixed' => true],
            ['name' => 'Meal Allowance', 'category' => 'allowance', 'is_fixed' => true],
            ['name' => 'Service Allowance', 'category' => 'allowance', 'is_fixed' => false],
        ];

        foreach ($components as $c) {
            \Modules\MasterData\Models\RemunerationComponent::updateOrCreate(
                ['name' => $c['name']],
                $c
            );
        }

        $transport = \Modules\MasterData\Models\RemunerationComponent::where('name', 'Transport Allowance')->first();
        $meal = \Modules\MasterData\Models\RemunerationComponent::where('name', 'Meal Allowance')->first();

        $security = \Modules\MasterData\Models\JobPosition::updateOrCreate(
            ['name' => 'Security Guard'],
            [
                'basic_salary' => 3500000,
                'risk_level' => 'low',
                'is_labor_intensive' => false,
            ]
        );

        $security->remunerationComponents()->syncWithoutDetaching([
            $transport->id => ['amount' => 500000],
            $meal->id => ['amount' => 300000],
        ]);

        $admin = \Modules\MasterData\Models\JobPosition::updateOrCreate(
            ['name' => 'Admin Staff'],
            [
                'basic_salary' => 4500000,
                'risk_level' => 'very_low',
                'is_labor_intensive' => false,
            ]
        );

        $admin->remunerationComponents()->syncWithoutDetaching([
            $transport->id => ['amount' => 600000],
            $meal->id => ['amount' => 400000],
        ]);
    }
}
