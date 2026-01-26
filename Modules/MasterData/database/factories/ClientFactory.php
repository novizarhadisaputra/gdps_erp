<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\MasterData\Models\Client::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('CL-####'),
            'legal_entity_type' => $this->faker->randomElement(['PT', 'CV', 'UD', 'Firma', 'Koperasi', 'Yayasan', 'Individual']),
            'name' => $this->faker->company(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'status' => 'active',
        ];
    }
}
