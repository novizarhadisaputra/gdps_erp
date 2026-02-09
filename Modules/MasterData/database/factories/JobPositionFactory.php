<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\JobPosition;

class JobPositionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = JobPosition::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'basic_salary' => $this->faker->numberBetween(5000000, 15000000),
            'risk_level' => $this->faker->randomElement(['very_low', 'low', 'medium', 'high', 'very_high']),
            'is_labor_intensive' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
