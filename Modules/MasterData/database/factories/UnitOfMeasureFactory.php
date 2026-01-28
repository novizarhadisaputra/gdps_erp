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
            'code' => 'UOM-' . mt_rand(100, 999),
            'name' => 'UOM ' . mt_rand(1, 100),
        ];
    }
}
