<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class ManpowerCostingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ManpowerCostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManpowerCostingService;
        // Seed full Master Data for realistic calculations
        $this->seed(MasterDataDatabaseSeeder::class);
    }

    /**
     * Test calculation based on spreadsheet COSTING MP R1 data.
     * Jabatan: Chief Security
     * Gaji Pokok: 11,379,412.94
     * Tunjangan Jabatan: 1,000,000 (Fixed)
     * Tunjangan Komunikasi: 250,000 (Fixed)
     * Tunjangan Kehadiran: 500,000 (Non-fixed)
     */
    public function test_manpower_costing_calculation_matches_spreadsheet(): void
    {
        // 1. Setup Master Data for specific test area
        $area = ProjectArea::create(['name' => 'Rembang (Test)', 'is_active' => true]);
        MinimumWage::create([
            'project_area_id' => $area->id,
            'year' => 2026,
            'amount' => 2327386.07, // UMK Rembang
            'province' => 'Jawa Tengah',
            'is_active' => true,
        ]);

        $basicSalary = 11379412.94;
        $allowances = [
            ['name' => 'Tunjangan Jabatan', 'amount' => 1000000, 'is_fixed' => true, 'is_bpjs_base' => true],
            ['name' => 'Tunjangan Komunikasi', 'amount' => 250000, 'is_fixed' => true, 'is_bpjs_base' => true],
            ['name' => 'Tunjangan Kehadiran', 'amount' => 500000, 'is_fixed' => false, 'is_bpjs_base' => false],
        ];

        // 2. Perform Calculation
        $result = $this->service->calculate(
            basicSalary: $basicSalary,
            allowances: $allowances,
            projectAreaId: $area->id,
            year: 2026,
            riskLevel: 'low',
            isLaborIntensive: false,
            adminFeePercentage: 10.0,
        );

        // 3. Assertions
        $this->assertEquals(11379412.94 + 1000000 + 250000, $result['upah']);
        $this->assertEquals($result['upah'] + 500000, $result['total_monthly_salary']);

        // BPJS Health (Employer 4%) capped at 12M
        $this->assertEquals(480000, $result['bpjs_health']['employer']);

        $this->assertGreaterThan(0, $result['bpjs_total']);
        $this->assertGreaterThan(0, $result['total_direct_cost']);
    }

    /**
     * Test UMK Fallback logic.
     */
    public function test_manpower_costing_uses_umk_if_salary_is_zero(): void
    {
        $area = ProjectArea::create(['name' => 'Batam (Test)', 'is_active' => true]);
        MinimumWage::create([
            'project_area_id' => $area->id,
            'year' => 2026,
            'amount' => 4685000,
            'province' => 'Kepulauan Riau',
            'is_active' => true,
        ]);

        $result = $this->service->calculate(
            basicSalary: 0, // Should fallback to UMK
            allowances: [],
            projectAreaId: $area->id,
            year: 2026
        );

        $this->assertEquals(4685000, $result['breakdown']['Gaji Pokok']);
    }

    /**
     * Test BPJS Health Cap (12M).
     */
    public function test_bpjs_health_is_capped_at_12_million(): void
    {
        $result = $this->service->calculate(
            basicSalary: 20000000, // Above 12M cap
            allowances: [],
            projectAreaId: null,
            year: 2026
        );

        $this->assertEquals(480000, $result['bpjs_health']['employer']);
    }

    /**
     * Test Progressive Tax (Pasal 17) Scheme.
     */
    public function test_manpower_costing_with_progressive_tax_scheme(): void
    {
        $result = $this->service->calculate(
            basicSalary: 15000000,
            allowances: [],
            projectAreaId: null,
            year: 2026,
            useTerMethod: false
        );

        $this->assertGreaterThan(0, $result['pph21']['total']);
    }
}
