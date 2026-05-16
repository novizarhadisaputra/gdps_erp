<?php

namespace Modules\Logistics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Logistics\Models\Warehouse;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->city().' Warehouse',
            'code' => $this->faker->unique()->lexify('WH-????'),
            'address' => $this->faker->address(),
            'is_active' => true,
        ];
    }
}
