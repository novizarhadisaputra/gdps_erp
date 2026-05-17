<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\WorkPattern;

class WorkPatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patterns = [
            ['name' => '5 Hari Kerja', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => false, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => true],
            ['name' => '6 Hari Kerja', 'days_per_week' => 6, 'hours_per_day' => 7.00, 'is_shift' => false, 'description' => '25 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => '3 Shift', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => true, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'Remote / Hybrid', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => false, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'On-Call', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => false, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'Part Time', 'days_per_week' => 2, 'hours_per_day' => 8.00, 'is_shift' => false, 'description' => '10 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'Split Shift', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => true, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'Flexible Hour', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => false, 'description' => '21 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
            ['name' => 'Roster / Rotational', 'days_per_week' => 5, 'hours_per_day' => 8.00, 'is_shift' => true, 'description' => '15 Hari Kerja/Bulan', 'is_active' => true, 'is_default' => false],
        ];

        foreach ($patterns as $pattern) {
            WorkPattern::updateOrCreate(
                ['name' => $pattern['name']],
                $pattern
            );
        }
    }
}
