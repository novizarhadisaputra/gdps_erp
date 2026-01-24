<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BillingOptionFactory extends Factory
{
    protected $model = \Modules\MasterData\Models\BillingOption::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('OPT-####'),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
