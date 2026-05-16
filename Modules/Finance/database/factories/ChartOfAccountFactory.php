<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Models\ChartOfAccount;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('#####'),
            'name' => $this->faker->words(3, true),
            'account_type' => 'asset',
            'is_active' => true,
        ];
    }
}
