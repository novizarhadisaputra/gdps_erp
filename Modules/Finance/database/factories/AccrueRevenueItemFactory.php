<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Models\AccrueRevenue;
use Modules\Finance\Models\AccrueRevenueItem;

class AccrueRevenueItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = AccrueRevenueItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'accrue_revenue_id' => AccrueRevenue::factory(),
            'description' => $this->faker->sentence,
            'amount_estimated' => 1000000,
            'amount_actual' => 1000000,
            'is_reversed' => false,
        ];
    }
}
