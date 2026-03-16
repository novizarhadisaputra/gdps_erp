<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\ProjectReview;

class ProjectReviewFactory extends Factory
{
    protected $model = ProjectReview::class;

    public function definition(): array
    {
        return [
            'lead_id' => \Modules\CRM\Models\Lead::factory(),
            'status' => $this->faker->randomElement(['draft', 'reviewing', 'approved', 'rejected']),
            'revision_number' => 1,
        ];
    }
}
