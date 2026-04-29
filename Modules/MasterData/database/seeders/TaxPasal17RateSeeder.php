<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\TaxPasal17Rate;

class TaxPasal17RateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rates = [
            ['min_amount' => 0, 'max_amount' => 60000000, 'rate' => 5],
            ['min_amount' => 60000001, 'max_amount' => 250000000, 'rate' => 15],
            ['min_amount' => 250000001, 'max_amount' => 500000000, 'rate' => 25],
            ['min_amount' => 500000001, 'max_amount' => 5000000000, 'rate' => 30],
            ['min_amount' => 5000000001, 'max_amount' => null, 'rate' => 35],
        ];

        foreach ($rates as $rate) {
            TaxPasal17Rate::updateOrCreate(
                ['min_amount' => $rate['min_amount']],
                $rate
            );
        }
    }
}
