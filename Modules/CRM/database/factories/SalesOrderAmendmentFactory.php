<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;

class SalesOrderAmendmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SalesOrderAmendment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'sales_order_id' => SalesOrder::factory(),
            'amendment_number' => $this->faker->unique()->word,
            'amendment_date' => $this->faker->date(),
            'status' => 'draft',
        ];
    }
}
