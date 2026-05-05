<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Enums\AccrueRevenueStatus;
use Modules\Finance\Models\AccrueRevenue;

class AccrueRevenueFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AccrueRevenue::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'project_id' => \Modules\Project\Models\Project::factory(),
            'year' => (int) date('Y'),
            'month' => (int) date('n'),
            'status' => AccrueRevenueStatus::Draft,
            'total_amount_estimated' => 1000000,
            'total_amount_actual' => 0,
        ];
    }
}
