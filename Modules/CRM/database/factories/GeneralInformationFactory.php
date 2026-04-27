<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Customer;
use Modules\MasterData\Models\ProjectArea;

class GeneralInformationFactory extends Factory
{
    protected $model = GeneralInformation::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('GI/###/??'),
            'lead_id' => Lead::factory(),
            'customer_id' => Customer::factory(),
            'project_area_id' => ProjectArea::factory(),
            'status' => 'draft',
            'scope_of_work' => $this->faker->sentence(),
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory(),
            'estimated_start_date' => now(),
            'estimated_end_date' => now()->addMonth(),
            'manpower_qualifications' => $this->faker->paragraph(),
            'work_activities' => $this->faker->paragraph(),
            'service_level' => $this->faker->paragraph(),
            'billing_requirements' => $this->faker->paragraph(),
        ];
    }
}
