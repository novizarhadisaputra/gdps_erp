<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\ContractType;

class ContractTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ContractType::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->lexify('CT-???'),
            'name' => $this->faker->words(2, true),
            'is_active' => true,
        ];
    }
}
