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
            'code' => 'ITEM-' . mt_rand(1000, 9999) . '-' . microtime(true),
            'name' => 'Item ' . mt_rand(1, 1000),
            'description' => 'Item Description',
            'is_active' => true,
        ];
    }
}
