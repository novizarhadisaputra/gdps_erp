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
            'customer_id' => \Modules\CRM\Models\Customer::factory(),
            'project_number' => $this->faker->unique()->bothify('####'),
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory(),
            'product_cluster_id' => \Modules\MasterData\Models\ProductCluster::factory(),
            'tax_id' => \Modules\MasterData\Models\Tax::factory(),
            'project_area_id' => \Modules\MasterData\Models\ProjectArea::factory(),
            'oprep_id' => \Modules\MasterData\Models\Employee::factory(),
            'ams_id' => \Modules\MasterData\Models\Employee::factory(),
        ];
    }
}
