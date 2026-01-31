<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\Customer;
use Modules\MasterData\Models\WorkScheme;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'customer_id' => Customer::factory(),
            'work_scheme_id' => WorkScheme::factory(),
            'status' => $this->faker->randomElement(LeadStatus::cases()),
            'estimated_amount' => $this->faker->numberBetween(10000000, 1000000000),
            'probability' => $this->faker->numberBetween(10, 90),
            'expected_closing_date' => $this->faker->dateTimeBetween('now', '+6 months'),
            'description' => $this->faker->paragraph(),
        ];
    }
}
