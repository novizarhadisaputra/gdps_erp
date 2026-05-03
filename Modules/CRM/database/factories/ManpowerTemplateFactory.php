<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\WorkScheme;

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
            'project_area_id' => ProjectArea::factory(),
            'work_scheme_id' => WorkScheme::factory(),
        ];
    }

    public function forLead(\Modules\CRM\Models\Lead $lead): self
    {
        return $this->state([
            'lead_id' => $lead->id,
            'project_area_id' => $lead->project_area_id,
            'work_scheme_id' => $lead->work_scheme_id,
        ]);
    }
}
