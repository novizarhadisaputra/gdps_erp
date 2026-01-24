<?php

namespace Modules\Project\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Project\Models\Project::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(3),
            'client_id' => \Modules\MasterData\Models\Client::factory(),
            'project_number' => '0001',
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory(),
            'product_cluster_id' => \Modules\MasterData\Models\ProductCluster::factory(),
            'tax_id' => \Modules\MasterData\Models\Tax::factory(),
            'project_area_id' => \Modules\MasterData\Models\ProjectArea::factory(),
        ];
    }
}
