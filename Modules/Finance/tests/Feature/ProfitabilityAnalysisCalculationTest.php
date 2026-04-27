<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Schemas\ProfitabilityAnalysisForm;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Traits\HasProfitabilityAnalysisActions;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\Finance\Models\ProfitabilityThreshold;
use Modules\MasterData\Models\DirectCostCategory;
use Tests\TestCase;

class ProfitabilityAnalysisCalculationTest extends TestCase
{
    use HasProfitabilityAnalysisActions;
    use RefreshDatabase;

    public function test_calculate_direct_cost_with_indirect_costs()
    {
        $manpowerCat = DirectCostCategory::firstOrCreate(['code' => 'manpower'], ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Manpower']);
        $toolsCat = DirectCostCategory::firstOrCreate(['code' => 'tools_equipment'], ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Tools & Equipment']);

        $get = function ($path) use ($manpowerCat, $toolsCat) {
            $data = [
                '/data.general_information_id' => null,
                '/data.manpowerItems' => [
                    [
                        'quantity' => 10,
                        'unit_cost_price' => 5000000,
                        'duration_months' => 12,
                        'markup_percentage' => 10,
                        'is_manpower' => true,
                        'direct_cost_category_id' => $manpowerCat->id,
                    ],
                ],
                '/data.operationalItems' => [
                    [
                        'quantity' => 2,
                        'unit_cost_price' => 10000000,
                        'depreciation_months' => 10,
                        'duration_months' => 12,
                        'markup_percentage' => 20,
                        'direct_cost_category_id' => $toolsCat->id,
                    ],
                ],
                '/data.management_fee_rate' => 5,
                '/data.indirect_mgmt_expenses' => 1000000,
                '/data.indirect_entertainment' => 500000,
                '/data.indirect_concession' => 0,
                '/data.indirect_business_partners' => [
                    ['amount' => 2000000],
                ],
                '/data.management_expense_rate' => 2,
                '/data.interest_rate' => 1,
                '/data.tax_rate' => 22,
                '/data.payment_term_id' => null,
            ];

            return $data[$path] ?? null;
        };

        $setResults = [];
        $set = function ($path, $value) use (&$setResults) {
            $setResults[$path] = $value;
        };

        try {
            ProfitabilityAnalysisForm::calculateDirectCost($get, $set, '/data.');

            $this->assertArrayHasKey('/data.direct_cost', $setResults);
            $this->assertArrayHasKey('/data.ebitda', $setResults);
            $this->assertArrayHasKey('/data.net_profit', $setResults);

        } catch (\Throwable $e) {
            $this->fail('calculateDirectCost failed: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
        }
    }

    public function test_calculate_direct_cost_with_manual_entry()
    {
        $manpowerCat = DirectCostCategory::firstOrCreate(['code' => 'manpower'], ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Manpower']);
        $toolsCat = DirectCostCategory::firstOrCreate(['code' => 'tools_equipment'], ['id' => (string) \Illuminate\Support\Str::uuid(), 'name' => 'Tools & Equipment']);

        $data = [
            '/data.is_manual_cost' => true,
            '/data.analysis_details.manual_revenue' => 50000000,
            '/data.analysis_details.manual_costs' => [
                ['direct_cost_category_id' => $manpowerCat->id, 'amount' => 20000000],
                ['direct_cost_category_id' => $toolsCat->id, 'amount' => 10000000],
            ],
            '/data.general_information_id' => null,
            '/data.management_fee_rate' => 0,
            '/data.management_fee' => 5000000,
            '/data.management_expense_rate' => 2,
            '/data.indirect_mgmt_expenses' => 0,
            '/data.indirect_entertainment' => 0,
            '/data.indirect_concession' => 0,
            '/data.indirect_business_partners' => [],
            '/data.interest_rate' => 0,
            '/data.tax_rate' => 22,
        ];

        $get = function ($path) use (&$data) {
            return $data[$path] ?? null;
        };

        $setResults = [];
        $set = function ($path, $value) use (&$data, &$setResults) {
            $data[$path] = $value;
            $setResults[$path] = $value;
        };
        ProfitabilityAnalysisForm::calculateDirectCost($get, $set, '/data.');

        // Direct Cost = 20M + 10M = 30M
        $this->assertEquals(30000000, $setResults['/data.direct_cost']);
        // Revenue = 30M (Costs) + 5M (MGMT Fee) = 35M
        $this->assertEquals(35000000, $setResults['/data.revenue_per_month']);
    }

    public function test_validate_profitability_thresholds()
    {

        ProfitabilityThreshold::create([
            'name' => 'Default',
            'min_gpm' => 15.00,
            'min_npm' => 5.00,
        ]);

        $pa = new ProfitabilityAnalysis;
        $pa->margin_percentage = 10.00; // Below 15%
        $pa->net_profit_margin = 8.00;

        $result = $this->validateProfitability($pa);
        $this->assertFalse($result);

        $pa->margin_percentage = 20.00;
        $pa->net_profit_margin = 4.00; // Below 5%
        $result = $this->validateProfitability($pa);
        $this->assertFalse($result);

        $pa->margin_percentage = 20.00;
        $pa->net_profit_margin = 6.00;
        $result = $this->validateProfitability($pa);
        $this->assertTrue($result);
    }
}
