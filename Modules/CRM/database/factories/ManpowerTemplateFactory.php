<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\ManpowerTemplate;

class ManpowerTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ManpowerTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }

    public function forLead(\Modules\CRM\Models\Lead $lead): self
    {
        return $this->state([
            'lead_id' => $lead->id,
        ]);
    }
}
