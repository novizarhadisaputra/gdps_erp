<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\RevenueType;

class RevenueTypeFactory extends Factory
{
    protected $model = RevenueType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'code' => $this->faker->unique()->word,
            'is_active' => true,
            'is_default' => false,
            'applicable_to' => ['accrual', 'revenue'],
        ];
    }
}
