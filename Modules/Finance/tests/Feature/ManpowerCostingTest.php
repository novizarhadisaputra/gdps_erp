<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Models\HealthConfig;
use Modules\MasterData\Models\JhtConfig;
use Modules\MasterData\Models\JkkConfig;
use Modules\MasterData\Models\JkmConfig;
use Modules\MasterData\Models\JpConfig;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\RegencyMinimumWage;
use Modules\MasterData\Models\TaxRateTer;
use Modules\MasterData\Models\Unit;
use Tests\TestCase;

class ManpowerCostingTest extends TestCase
{
    use RefreshDatabase;

    protected ManpowerCostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManpowerCostingService;
        $this->setupConfigs();
    }

    protected function setupConfigs(): void
    {
        // Setup Unit and Project Area for FK
        $unit = Unit::create([
            'code' => 'UNIT_1',
            'name' => 'Unit 1',
        ]);

        $area = ProjectArea::create([
            'unit_id' => $unit->id,
            'code' => 'AREA_1',
            'name' => 'Area 1',
        ]);

        // Setup UMK
        RegencyMinimumWage::create([
            'project_area_id' => $area->id,
            'year' => 2024,
            'amount' => 3000000,
        ]);

        // Setup BPJS Health
        HealthConfig::create([
            'name' => 'Health Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.04,
            'employee_rate' => 0.01,
            'floor_type' => 'umk',
            'cap_nominal' => 12000000,
            'is_active' => true,
        ]);

        // Setup BPJS Ketenagakerjaan
        JkkConfig::create([
            'name' => 'JKK Config',
            'employee_type' => 'ppu',
            'risk_level' => 'very_low',
            'employer_rate' => 0.0024,
            'employee_rate' => 0,
            'has_tier' => false,
            'is_active' => true,
        ]);

        JkmConfig::create([
            'name' => 'JKM Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.003,
            'employee_rate' => 0,
            'is_active' => true,
        ]);

        JhtConfig::create([
            'name' => 'JHT Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.037,
            'employee_rate' => 0.02,
            'has_tier' => false,
            'is_active' => true,
        ]);

        JpConfig::create([
            'name' => 'JP Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.02,
            'employee_rate' => 0.01,
            'cap_nominal' => 10000000, // Example capping
            'is_active' => true,
        ]);

        // Setup PTKP & TER
        PtkpConfig::create([
            'name' => 'TK/0',
            'code' => 'TK/0',
            'tax_category' => 'A',
            'annual_amount' => 54000000,
        ]);

        TaxRateTer::create([
            'category' => 'A',
            'min_gross' => 0,
            'max_gross' => 5400000,
            'rate' => 0,
        ]);

        TaxRateTer::create([
            'category' => 'A',
            'min_gross' => 5400001,
            'max_gross' => 5650000,
            'rate' => 0.25,
        ]);
    }

    public function test_calculate_with_health_bpjs_capping_and_floor(): void
    {
        $area = ProjectArea::where('code', 'AREA_1')->first();

        // Scenario 1: Salary below UMK (should floor to UMK)
        $result = $this->service->calculate(
            basicSalary: 2500000, // Below UMK 3,000,000
            allowances: [],
            projectAreaId: $area->id,
            year: 2024
        );
        // Base for Health should be 3,000,000 (UMK Floor)
        // 4% * 3,000,000 = 120,000
        $this->assertEquals(120000, $result['bpjs_health']['employer_total']);

        // Scenario 2: Salary above Cap (should cap to 12,000,000)
        $result = $this->service->calculate(
            basicSalary: 15000000, // Above Cap 12,000,000
            allowances: [],
            projectAreaId: $area->id,
            year: 2024
        );
        // 4% * 12,000,000 = 480,000
        $this->assertEquals(480000, $result['bpjs_health']['employer_total']);
    }

    public function test_pph21_ter_2024_category_a_lookup(): void
    {
        $area = ProjectArea::where('code', 'AREA_1')->first();

        // Bruto = Upah (5.5M) + BPJS Employer (~360k) + THR/Comp (~920k) = ~6.8M
        // Range 6.75M - 7.5M for Category A is 1.25%
        TaxRateTer::create([
            'category' => 'A',
            'min_gross' => 6750001,
            'max_gross' => 7500000,
            'rate' => 1.25,
            'is_active' => true,
        ]);

        $result = $this->service->calculate(
            basicSalary: 5000000,
            allowances: [
                ['value' => 500000, 'type' => 'nominal', 'is_fixed' => true],
            ],
            projectAreaId: $area->id,
            year: 2024,
            ptkpCode: 'TK/0'
        );

        $this->assertEquals('A', $result['pph21']['category']);
        $this->assertEquals(1.25, $result['pph21']['rate']);
        $this->assertGreaterThan(0, $result['pph21']['total']);
    }

    public function test_management_fee_calculation_on_subtotal(): void
    {
        $area = ProjectArea::where('code', 'AREA_1')->first();

        $result = $this->service->calculate(
            basicSalary: 5000000,
            allowances: [],
            projectAreaId: $area->id,
            year: 2024,
            adminFeePercentage: 10.0 // 10%
        );

        // Management fee should be 10% of (Subtotal A+B+C+D+E)
        $subtotal = $result['total_direct_cost'];
        $expectedFee = $subtotal * 0.1;

        $this->assertEquals($expectedFee, $result['admin_fee']);
        $this->assertEquals($subtotal + $expectedFee, $result['total_cost_to_company']);
    }
}
