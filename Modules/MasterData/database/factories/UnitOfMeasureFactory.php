<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UnitOfMeasureFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\MasterData\Models\UnitOfMeasure::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('UOM-####'),
            'name' => $this->faker->word(),
        ];
    }
}
