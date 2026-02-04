<?php

namespace Modules\MasterData\Database\Seeders;

use Illuminate\Database\Seeder;

class BpjsConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // JKK
            ['name' => 'JKK Very Low', 'type' => 'employment', 'category' => 'JKK', 'employer_rate' => 0.0024, 'employee_rate' => 0.0, 'risk_level' => 'very_low'],
            ['name' => 'JKK Low', 'type' => 'employment', 'category' => 'JKK', 'employer_rate' => 0.0054, 'employee_rate' => 0.0, 'risk_level' => 'low'],
            ['name' => 'JKK Medium', 'type' => 'employment', 'category' => 'JKK', 'employer_rate' => 0.0089, 'employee_rate' => 0.0, 'risk_level' => 'medium'],
            ['name' => 'JKK High', 'type' => 'employment', 'category' => 'JKK', 'employer_rate' => 0.0127, 'employee_rate' => 0.0, 'risk_level' => 'high'],
            ['name' => 'JKK Very High', 'type' => 'employment', 'category' => 'JKK', 'employer_rate' => 0.0174, 'employee_rate' => 0.0, 'risk_level' => 'very_high'],
            
            // JKM
            ['name' => 'JKM', 'type' => 'employment', 'category' => 'JKM', 'employer_rate' => 0.0030, 'employee_rate' => 0.0],
            
            // JHT
            ['name' => 'JHT', 'type' => 'employment', 'category' => 'JHT', 'employer_rate' => 0.0370, 'employee_rate' => 0.0200],
            
            // JP
            ['name' => 'JP', 'type' => 'employment', 'category' => 'JP', 'employer_rate' => 0.0200, 'employee_rate' => 0.0100, 'cap_type' => 'nominal', 'cap_nominal' => 10547400],
            
            // Health
            ['name' => 'BPJS Kesehatan', 'type' => 'health', 'category' => 'Health', 'employer_rate' => 0.0400, 'employee_rate' => 0.0100, 'floor_type' => 'umk', 'cap_type' => 'nominal', 'cap_nominal' => 12000000],
        ];

        foreach ($configs as $config) {
            \Modules\MasterData\Models\BpjsConfig::updateOrCreate(
                ['name' => $config['name']],
                $config
            );
        }
    }
}
