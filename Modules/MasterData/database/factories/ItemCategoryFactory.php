<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\MasterData\Models\ItemCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => 'CAT-' . mt_rand(100, 999),
            'name' => 'Category ' . mt_rand(1, 100),
            'description' => 'Category Description',
        ];
    }
}
