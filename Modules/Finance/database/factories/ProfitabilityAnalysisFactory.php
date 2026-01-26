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
            'project_number' => $this->faker->randomNumber(),
            'status' => 'draft',
            'revenue_per_month' => $this->faker->randomFloat(2, 1000, 10000),
            'direct_cost' => $this->faker->randomFloat(2, 500, 5000),
            'management_fee' => $this->faker->randomFloat(2, 100, 1000),
            'margin_percentage' => $this->faker->randomFloat(2, 5, 20),
        ];
    }
}
