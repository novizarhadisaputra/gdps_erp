<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\CostingTemplateItem;

class CostingTemplateItemFactory extends Factory
{
    protected $model = CostingTemplateItem::class;

    public function definition(): array
    {
        return [
            'category' => \Modules\CRM\Enums\CostingCategory::ToolsEquipment,
            'name' => $this->faker->words(3, true),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $this->faker->numberBetween(1000, 100000),
            'monthly_cost' => $this->faker->numberBetween(1000, 100000),
            'total_price' => $this->faker->numberBetween(1000, 1000000),
        ];
    }
}
