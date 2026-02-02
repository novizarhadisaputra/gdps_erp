<?php

namespace Modules\MasterData\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\MasterData\Enums\AssetGroupType;
use Modules\MasterData\Models\AssetGroup;

class AssetGroupFactory extends Factory
{
    protected $model = AssetGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'type' => AssetGroupType::TangibleNonBuilding,
            'useful_life_years' => 4,
            'rate_straight_line' => 25,
            'rate_declining_balance' => 50,
            'description' => $this->faker->sentence,
        ];
    }
}
