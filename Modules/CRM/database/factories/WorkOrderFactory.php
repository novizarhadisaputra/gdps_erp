<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\WorkOrder;

class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('WO/####/??'),
            'order_date' => now(),
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'status' => 'draft',
        ];
    }
}
