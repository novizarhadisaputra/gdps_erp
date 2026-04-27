<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesOrder;
use Modules\Project\Models\Project;

class SalesOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SalesOrder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('GDPS/UB/SO-###/??'),
            'order_date' => $this->faker->date(),
            'project_id' => Project::factory(),
            'proposal_id' => Proposal::factory(),
            'customer_id' => Customer::factory(),
            'type' => 'external',
            'status' => 'draft',
            'amount' => $this->faker->randomFloat(2, 1000000, 100000000),
        ];
    }
}
