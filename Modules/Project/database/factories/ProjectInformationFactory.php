<?php

namespace Modules\Project\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Project\Models\Project;

class ProjectInformationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Project\Models\ProjectInformation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'pic_client_name' => $this->faker->name(),
            'pic_client_phone' => $this->faker->phoneNumber(),
            'status' => 'draft',
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ];
    }
}
