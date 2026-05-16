<?php

namespace Modules\Logistics\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Logistics\Enums\PurchaseRequestStatus;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\MasterData\Models\Employee;
use Modules\Project\Models\Project;

class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        return [
            'pr_number' => 'PR/'.now()->format('Y/m').'/'.$this->faker->unique()->numerify('####'),
            'project_id' => Project::factory(),
            'requester_id' => Employee::factory(),
            'total_amount' => $this->faker->randomFloat(2, 1000000, 50000000),
            'status' => PurchaseRequestStatus::Draft,
            'description' => $this->faker->sentence(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
