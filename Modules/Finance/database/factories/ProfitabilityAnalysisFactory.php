<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Models\ProfitabilityAnalysis;

class ProfitabilityAnalysisFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ProfitabilityAnalysis::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'project_number' => $this->faker->randomNumber(2),
            'status' => 'draft',
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'product_cluster_id' => \Modules\MasterData\Models\ProductCluster::factory(),
            'project_area_id' => \Modules\MasterData\Models\ProjectArea::factory(),
            'project_type_id' => \Modules\MasterData\Models\ProjectType::factory(),
            'tax_id' => \Modules\MasterData\Models\Tax::factory(),
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory(),
            'revenue_per_month' => 10000000,
            'direct_cost' => 8000000,
            'management_fee' => 1000000,
            'management_fee_rate' => 10.0,
            'margin_percentage' => 20.0,
            'tax_rate' => 11.0,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->startOfMonth()->addYear()->subDay(),
        ];
    }
}
