<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\RevenueSegment;

class RevenueSegmentFactory extends Factory
{
    protected $model = RevenueSegment::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('RS-####'),
            'name' => $this->faker->word(),
            'is_active' => true,
        ];
    }
}
