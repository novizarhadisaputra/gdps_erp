<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\CRM\Models\Contract::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}
