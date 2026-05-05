<?php

namespace Modules\Finance\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Finance\Enums\ProfitabilityAnalysisMonthlyStatus;
use Modules\Finance\Models\ProfitabilityAnalysisMonthly;

class ProfitabilityAnalysisMonthlyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = ProfitabilityAnalysisMonthly::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'profitability_analysis_id' => \Modules\Finance\Models\ProfitabilityAnalysis::factory(),
            'target_revenue' => 10000000,
            'forecast_revenue' => 10000000,
            'actual_revenue' => 0,
            'actual_cost' => 0,
            'actual_net_profit' => 0,
            'actual_margin_percentage' => 0,
            'actual_details' => [],
            'variance_revenue' => 0,
            'variance_profit' => 0,
            'month' => (int) date('m'),
            'year' => (int) date('Y'),
            'status' => ProfitabilityAnalysisMonthlyStatus::Draft,
        ];
    }
}
