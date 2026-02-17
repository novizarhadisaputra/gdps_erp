<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\Proposal;

class ProposalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Proposal::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'proposal_number' => $this->faker->unique()->bothify('PROP/####/'.date('Y')),
            'amount' => $this->faker->randomFloat(2, 1000000, 50000000),
            'status' => 'draft',
            'sequence_number' => $this->faker->numberBetween(1, 1000),
            'year' => date('Y'),
        ];
    }
}
