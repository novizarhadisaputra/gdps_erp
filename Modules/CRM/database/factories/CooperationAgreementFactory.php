<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\CooperationAgreement;

class CooperationAgreementFactory extends Factory
{
    protected $model = CooperationAgreement::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->bothify('PKS/####/??'),
            'agreement_date' => now(),
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'status' => 'draft',
        ];
    }
}
