<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\ManpowerTemplateItem;

class ManpowerTemplateItemFactory extends Factory
{
    protected $model = ManpowerTemplateItem::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(1, 10),
            'basic_salary' => $this->faker->numberBetween(5000000, 15000000),
            'notes' => $this->faker->sentence(),
        ];
    }
}
