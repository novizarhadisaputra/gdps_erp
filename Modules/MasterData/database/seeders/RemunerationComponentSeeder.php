<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\MasterData\Models\RemunerationComponent;

class RemunerationComponentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $components = [
            [
                'name' => 'Gaji Pokok',
                'type' => 'benefit',
                'default_amount' => 5000000,
                'is_bpjs_base' => true,
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Jabatan',
                'type' => 'fixed_allowance',
                'default_amount' => 1000000,
                'is_bpjs_base' => true,
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Makan',
                'type' => 'non_fixed_allowance',
                'default_amount' => 500000,
                'is_bpjs_base' => false,
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tunjangan Transport',
                'type' => 'non_fixed_allowance',
                'default_amount' => 500000,
                'is_bpjs_base' => false,
                'is_taxable' => true,
                'is_active' => true,
            ],
            [
                'name' => 'JKK',
                'type' => 'benefit',
                'default_amount' => 0,
                'is_bpjs_base' => false,
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'JKM',
                'type' => 'benefit',
                'default_amount' => 0,
                'is_bpjs_base' => false,
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'JHT',
                'type' => 'benefit',
                'default_amount' => 0,
                'is_bpjs_base' => false,
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'JP',
                'type' => 'benefit',
                'default_amount' => 0,
                'is_bpjs_base' => false,
                'is_taxable' => false,
                'is_active' => true,
            ],
            [
                'name' => 'BPJS Kesehatan',
                'type' => 'benefit',
                'default_amount' => 0,
                'is_bpjs_base' => false,
                'is_taxable' => false,
                'is_active' => true,
            ],
        ];

        foreach ($components as $component) {
            RemunerationComponent::updateOrCreate(
                ['name' => $component['name']],
                $component
            );
        }
    }
}
