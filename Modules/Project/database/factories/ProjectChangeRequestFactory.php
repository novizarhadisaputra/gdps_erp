<?php

namespace Modules\Project\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Project\Enums\ProjectChangeRequestStatus;
use Modules\Project\Enums\ProjectChangeRequestType;
use Modules\Project\Models\Project;
use Modules\Project\Models\ProjectChangeRequest;

class ProjectChangeRequestFactory extends Factory
{
    protected $model = ProjectChangeRequest::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'number' => $this->faker->unique()->bothify('PCR-####'),
            'sequence_number' => $this->faker->numberBetween(1, 10),
            'year' => now()->year,
            'type' => ProjectChangeRequestType::Manpower,
            'notes' => $this->faker->paragraph(),
            'snapshot' => [],
            'status' => ProjectChangeRequestStatus::Draft,
        ];
    }
}
