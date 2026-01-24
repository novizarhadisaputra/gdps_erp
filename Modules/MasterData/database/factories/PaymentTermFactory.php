<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentTermFactory extends Factory
{
    protected $model = \Modules\MasterData\Models\PaymentTerm::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('TOP-####'),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
