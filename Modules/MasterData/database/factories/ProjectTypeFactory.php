<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectTypeFactory extends Factory
{
    protected $model = \Modules\MasterData\Models\ProjectType::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('PT-####'),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
