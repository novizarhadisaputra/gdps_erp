<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\ServiceLine;

class ServiceLineFactory extends Factory
{
    protected $model = ServiceLine::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('SL-####'),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
