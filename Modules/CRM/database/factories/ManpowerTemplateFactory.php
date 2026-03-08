<?php

namespace Modules\CRM\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\ContractType;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\WorkScheme;

class ManpowerTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ManpowerTemplate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_active' => true,
            'project_area_id' => ProjectArea::factory(),
            'contract_type_id' => ContractType::factory(),
            'work_scheme_id' => WorkScheme::factory(),
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
            'is_labor_intensive' => false,
            'bill_thr_monthly' => true,
            'bill_compensation_monthly' => true,
        ];
    }
}
