<?php

namespace Modules\CRM\Database\Seeders;

use Illuminate\Database\Seeder;

class CRMDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Modules\CRM\Models\Lead::factory()->count(5)->create();

        $this->call([
            SalesPlanSeeder::class,
        ]);
    }
}
