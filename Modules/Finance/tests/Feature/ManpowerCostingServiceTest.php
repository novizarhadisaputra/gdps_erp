<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\ThrBasisType;
use Tests\TestCase;

class ManpowerCostingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ManpowerCostingService $service;

    protected ManpowerTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManpowerCostingService;

        // Seed full Master Data for realistic calculations
        $this->seed(MasterDataDatabaseSeeder::class);

        // Create a default template for items
        $this->template = ManpowerTemplate::factory()->create();
    }

    /**
     * Skenario Utama: Chief Security (Spreadsheet Row 7)
     * - Validasi integrasi dengan CRM ManpowerTemplateItem
     */
    public function test_manpower_costing_matches_spreadsheet_chief_security(): void
    {
        $bpjsBasis = BpjsBasisType::where('formula_code', 'gaji_plus_tunjangan_tetap')->first();
        $thrBasis = ThrBasisType::where('formula_code', 'gaji_plus_tetap')->first();
        $jobPosition = JobPosition::factory()->create(['name' => 'Chief Security']);

        $allowances = [
            ['name' => 'Tunjangan Tetap', 'amount' => 315000, 'is_fixed' => true, 'is_bpjs_base' => true],
            ['name' => 'Tunjangan Transport', 'amount' => 525000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan Makan', 'amount' => 420000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan Shift', 'amount' => 630000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan HP', 'amount' => 420000, 'is_fixed' => false, 'is_bpjs_base' => false],
        ];

        $item = ManpowerTemplateItem::factory()->create([
            'manpower_template_id' => $this->template->id,
            'job_position_id' => $jobPosition->id,
            'basic_salary' => 5000000,
            'allowances' => $allowances,
            'risk_level' => 'very_low', // 0.24%
            'ptkp_status' => 'TK/0',
            'bpjs_basis_id' => $bpjsBasis?->id,
            'thr_basis_id' => $thrBasis?->id,
            'is_bpjs_active' => true,
            'use_ter_method' => true,
            'is_employee_jkn_borne_by_company' => true,
            'is_employee_jkk_borne_by_company' => true,
            'is_employee_jkm_borne_by_company' => true,
            'is_employee_jht_borne_by_company' => true,
            'is_employee_jp_borne_by_company' => true,
        ]);

        $result = $this->service->calculateForTemplateItem($item);

        // Assertions based on 2024 regulations
        $this->assertEquals(5315000, $result['upah']);
        $this->assertEquals(1995000, $result['total_non_fixed_allowances']);
        $this->assertEquals(302955, $result['bpjs_employment']['details']['jht']['line_total']);
        $this->assertEqualsWithDelta(116458, $result['pph21']['total'], 1.0);
        $this->assertEqualsWithDelta(8952690, $result['total_direct_cost'], 10.0);
    }

    /**
     * Test calculation based on raw direct service call.
     */
    public function test_manpower_costing_calculation_direct_service_call(): void
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
        $this->assertGreaterThan(0, $result['bpjs_total']);
    }

    /**
     * Skenario: BPJS Standar (Potong Gaji Karyawan) + Pajak Progresif
     */
    public function test_standard_bpjs_and_progressive_tax(): void
    {
        $jobPosition = JobPosition::factory()->create(['name' => 'Security Guard']);

        $item = ManpowerTemplateItem::factory()->create([
            'manpower_template_id' => $this->template->id,
            'job_position_id' => $jobPosition->id,
            'basic_salary' => 5000000,
            'allowances' => [],
            'risk_level' => 'low', // 0.54%
            'ptkp_status' => 'TK/0',
            'use_ter_method' => false, // Progressive
            'is_employee_jkn_borne_by_company' => false,
            'is_employee_jht_borne_by_company' => false,
            'is_employee_jp_borne_by_company' => false,
            'is_bpjs_active' => true,
        ]);

        $result = $this->service->calculateForTemplateItem($item);

        // BPJS JKK (0.54% of 5m) = 27k, JKM 15k, JHT Er 185k, JP Er 100k, JKN Er 200k = 527k
        $this->assertEquals(527000, $result['bpjs_total']);
        $this->assertGreaterThan(0, $result['pph21']['total']);
    }

    /**
     * Skenario: Kategori Pajak TER Berbeda (Category B - K/1)
     */
    public function test_tax_ter_category_b_calculation(): void
    {
        $jobPosition = JobPosition::factory()->create(['name' => 'Supervisor']);

        $item = ManpowerTemplateItem::factory()->create([
            'manpower_template_id' => $this->template->id,
            'job_position_id' => $jobPosition->id,
            'basic_salary' => 10000000,
            'ptkp_status' => 'K/1', // Category B
            'use_ter_method' => true,
        ]);

        $result = $this->service->calculateForTemplateItem($item);

        $this->assertEquals('B', $result['pph21']['category']);
        $this->assertEquals(1.5, $result['pph21']['rate']);
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
}
