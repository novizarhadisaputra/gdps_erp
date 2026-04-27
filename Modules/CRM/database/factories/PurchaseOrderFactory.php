<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\PurchaseOrder;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('PO/####/??'),
            'order_date' => now(),
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'status' => 'draft',
        ];
    }
}
