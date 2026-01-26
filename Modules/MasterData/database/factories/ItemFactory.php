<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\UnitOfMeasure;

class ItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\MasterData\Models\Item::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'item_category_id' => ItemCategory::factory(),
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'code' => $this->faker->unique()->bothify('ITEM-####'),
            'name' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
