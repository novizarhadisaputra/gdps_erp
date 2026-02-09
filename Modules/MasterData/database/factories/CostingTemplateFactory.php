<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Models\CostingTemplate;

class CostingTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = CostingTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            // 'is_active' removed as it's not in migration
        ];
    }
}
