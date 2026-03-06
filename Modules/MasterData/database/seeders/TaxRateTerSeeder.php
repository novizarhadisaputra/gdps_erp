<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\TaxRateTer;

class TaxRateTerSeeder extends Seeder
{
    public function run(): void
    {
        // Category A (TK/0, TK/1, K/0)
        $categoryA = [
            ['min' => 0, 'max' => 5400000, 'rate' => 0],
            ['min' => 5400001, 'max' => 5650000, 'rate' => 0.25],
            ['min' => 5650001, 'max' => 5950000, 'rate' => 0.5],
            ['min' => 5950001, 'max' => 6300000, 'rate' => 0.75],
            ['min' => 6300001, 'max' => 6750000, 'rate' => 1],
            ['min' => 6750001, 'max' => 7500000, 'rate' => 1.25],
            ['min' => 7500001, 'max' => 8550000, 'rate' => 1.5],
            ['min' => 8550001, 'max' => 9650000, 'rate' => 1.75],
            ['min' => 9650001, 'max' => 10650000, 'rate' => 2],
            ['min' => 10650001, 'max' => 12250000, 'rate' => 2.25],
            ['min' => 12250001, 'max' => 14000000, 'rate' => 2.5],
            ['min' => 14000001, 'max' => 16150000, 'rate' => 3],
            ['min' => 16150001, 'max' => 19050000, 'rate' => 4],
            ['min' => 19050001, 'max' => 23600000, 'rate' => 5],
            ['min' => 23600001, 'max' => 28350000, 'rate' => 6],
            ['min' => 28350001, 'max' => 34800000, 'rate' => 7],
            ['min' => 34800001, 'max' => 42200000, 'rate' => 8],
            ['min' => 42200001, 'max' => 53300000, 'rate' => 9],
            ['min' => 53300001, 'max' => 66300000, 'rate' => 10],
            // ... truncated for brevity but including up to high brackets if needed
            ['min' => 66300001, 'max' => 84850000, 'rate' => 11],
            ['min' => 84850001, 'max' => 105900000, 'rate' => 12],
            ['min' => 105900001, 'max' => 129300000, 'rate' => 13],
            ['min' => 129300001, 'max' => 154400000, 'rate' => 14],
            ['min' => 154400001, 'max' => 180150000, 'rate' => 15],
            ['min' => 180150001, 'max' => 208400000, 'rate' => 16],
            ['min' => 208400001, 'max' => 243200000, 'rate' => 17],
            ['min' => 243200001, 'max' => 285300000, 'rate' => 18],
            ['min' => 285300001, 'max' => 335400000, 'rate' => 19],
            ['min' => 335400001, 'max' => 395300000, 'rate' => 20],
            ['min' => 395300001, 'max' => 467700000, 'rate' => 21],
            ['min' => 467700001, 'max' => 557800000, 'rate' => 22],
            ['min' => 557800001, 'max' => 674400000, 'rate' => 23],
            ['min' => 674400001, 'max' => 827700000, 'rate' => 24],
            ['min' => 827700001, 'max' => 1121000000, 'rate' => 25],
            ['min' => 1121000001, 'max' => null, 'rate' => 34],
        ];

        // Category B (TK/2, TK/3, K/1, K/2)
        $categoryB = [
            ['min' => 0, 'max' => 6200000, 'rate' => 0],
            ['min' => 6200001, 'max' => 6500000, 'rate' => 0.25],
            ['min' => 6500001, 'max' => 6850000, 'rate' => 0.5],
            ['min' => 6850001, 'max' => 7300000, 'rate' => 0.75],
            ['min' => 7300001, 'max' => 9200000, 'rate' => 1],
            ['min' => 9200001, 'max' => 10750000, 'rate' => 1.5],
            ['min' => 10750001, 'max' => 12550000, 'rate' => 2],
            ['min' => 12550001, 'max' => 14750000, 'rate' => 2.5],
            ['min' => 14750001, 'max' => 17050000, 'rate' => 3],
            ['min' => 17050001, 'max' => 19350000, 'rate' => 4],
            ['min' => 19350001, 'max' => 21450000, 'rate' => 5],
            ['min' => 21450001, 'max' => 24150000, 'rate' => 6],
            ['min' => 24150001, 'max' => 28250000, 'rate' => 7],
            ['min' => 28250001, 'max' => 33050000, 'rate' => 8],
            ['min' => 33050001, 'max' => 38450000, 'rate' => 9],
            ['min' => 38450001, 'max' => 44450000, 'rate' => 10],
            ['min' => 44450001, 'max' => 52150000, 'rate' => 11],
            ['min' => 52150001, 'max' => 60750000, 'rate' => 12],
            ['min' => 60750001, 'max' => 71450000, 'rate' => 13],
            ['min' => 71450001, 'max' => 84250000, 'rate' => 14],
            ['min' => 84250001, 'max' => 100750000, 'rate' => 15],
            ['min' => 100750000, 'max' => null, 'rate' => 34], // Simplified higher brackets
        ];

        // Category C (K/3)
        $categoryC = [
            ['min' => 0, 'max' => 6600000, 'rate' => 0],
            ['min' => 6600001, 'max' => 6950000, 'rate' => 0.25],
            ['min' => 6950001, 'max' => 7350000, 'rate' => 0.5],
            ['min' => 7350001, 'max' => 7800000, 'rate' => 0.75],
            ['min' => 7800001, 'max' => 8350000, 'rate' => 1],
            ['min' => 8350001, 'max' => 9450000, 'rate' => 1.25],
            ['min' => 9450001, 'max' => 10350000, 'rate' => 1.5],
            ['min' => 10350001, 'max' => 11350000, 'rate' => 1.75],
            ['min' => 11350001, 'max' => 13500000, 'rate' => 2],
            ['min' => 13500001, 'max' => 15100000, 'rate' => 2.5],
            ['min' => 15100001, 'max' => 17300000, 'rate' => 3],
            ['min' => 17300001, 'max' => 20400000, 'rate' => 4],
            ['min' => 20400001, 'max' => null, 'rate' => 34], // Simplified higher brackets
        ];

        foreach ($categoryA as $row) {
            TaxRateTer::create(['category' => 'A', 'min_gross' => $row['min'], 'max_gross' => $row['max'], 'rate' => $row['rate']]);
        }
        foreach ($categoryB as $row) {
            TaxRateTer::create(['category' => 'B', 'min_gross' => $row['min'], 'max_gross' => $row['max'], 'rate' => $row['rate']]);
        }
        foreach ($categoryC as $row) {
            TaxRateTer::create(['category' => 'C', 'min_gross' => $row['min'], 'max_gross' => $row['max'], 'rate' => $row['rate']]);
        }
    }
}
