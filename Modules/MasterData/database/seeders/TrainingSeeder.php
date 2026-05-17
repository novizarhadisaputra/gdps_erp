<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\Training;

class TrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $trainings = [
            ['name' => 'Developing Service Communication and Attitude/Hospitality', 'base_cost' => 1000000.00, 'is_active' => true],
            ['name' => 'Human Factor', 'base_cost' => 2000000.00, 'is_active' => true],
            ['name' => 'Gada Pratama', 'base_cost' => 1500000.00, 'is_active' => true],
            ['name' => 'Gada Madya', 'base_cost' => 2000000.00, 'is_active' => true],
            ['name' => 'Gada Utama', 'base_cost' => 3000000.00, 'is_active' => true],
        ];

        foreach ($trainings as $training) {
            Training::updateOrCreate(['name' => $training['name']], $training);
        }
    }
}
