<?php

namespace Modules\Project\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\Project;
use Modules\Project\Models\WorkCompletionReport;

class WorkCompletionReportFactory extends Factory
{
    protected $model = WorkCompletionReport::class;

    public function definition(): array
    {
        $project = Project::factory()->create();

        return [
            'project_id' => $project->id,
            'customer_id' => $project->customer_id,
            'tax_id' => $project->tax_id,
            'project_area_id' => $project->project_area_id,
            'sourceable_id' => \Modules\CRM\Models\Proposal::factory(),
            'sourceable_type' => \Modules\CRM\Models\Proposal::class,
            'number' => $this->faker->unique()->bothify('WCR-####'),
            'document_date' => now()->format('Y-m-d'),
            'service_period_start' => now()->format('Y-m-d'),
            'service_period_end' => now()->addMonth()->format('Y-m-d'),
            'work_progress_percentage' => 100,
            'status' => WorkCompletionStatus::Draft,
            'content_config' => [
                'recipient_name' => $this->faker->name,
                'recipient_gender' => \Modules\MasterData\Enums\Gender::Male->value,
                'recipient_title' => 'Manager',
            ],
            'tax_basis' => 'total',
            'tax_percentage' => 12,
            'tax_wording' => [
                'id' => 'Pajak 12%',
                'en' => 'Tax 12%',
            ],
        ];
    }
}
