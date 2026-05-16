<?php

namespace Modules\Finance\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Services\ManpowerCostingService;
use Modules\MasterData\Database\Seeders\MasterDataDatabaseSeeder;
use Tests\TestCase;

class SpreadsheetValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ManpowerCostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ManpowerCostingService;

        // Seed Master Data (BPJS Rates, Tax Rates, PTKP, etc)
        $this->seed(MasterDataDatabaseSeeder::class);
    }

    /**
     * Validate against Spreadsheet "COSTING MP R1" Row 7: Chief Security
     */
    public function test_chief_security_calculation_matches_spreadsheet(): void
    {
        // 1. Setup Master Data Relationships
        $bpjsBasis = \Modules\MasterData\Models\BpjsBasisType::where('formula_code', 'gaji_plus_tunjangan_tetap')->first();
        $thrBasis = \Modules\MasterData\Models\ThrBasisType::where('formula_code', 'gaji_plus_tetap')->first();
        $jobPosition = \Modules\MasterData\Models\JobPosition::factory()->create(['name' => 'Chief Security']);

        // 2. Create Template (The context)
        $template = \Modules\CRM\Models\ManpowerTemplate::factory()->create([
            'year' => 2024,
        ]);

        // 3. Create Template Item (The data)
        // Data from Spreadsheet
        $allowances = [
            ['name' => 'Tunjangan Tetap', 'amount' => 315000, 'is_fixed' => true, 'is_bpjs_base' => true],
            ['name' => 'Tunjangan Kehadiran', 'amount' => 525000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan Transportasi', 'amount' => 420000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan Makan', 'amount' => 630000, 'is_fixed' => false, 'is_bpjs_base' => false],
            ['name' => 'Tunjangan Lembur', 'amount' => 420000, 'is_fixed' => false, 'is_bpjs_base' => false],
        ];

        $item = \Modules\CRM\Models\ManpowerTemplateItem::factory()->create([
            'manpower_template_id' => $template->id,
            'job_position_id' => $jobPosition->id,
            'basic_salary' => 5000000,
            'allowances' => $allowances,
            'risk_level' => 'very_low', // 0.24%
            'employee_type' => 'ppu',
            'ptkp_status' => 'TK/0',
            'bpjs_basis_id' => $bpjsBasis?->id,
            'thr_basis_id' => $thrBasis?->id,
            'compensation_basis_id' => $thrBasis?->id,
            'is_bpjs_active' => true,
            'use_ter_method' => true,
            'is_employee_jkn_borne_by_company' => true,
            'is_employee_jkk_borne_by_company' => true,
            'is_employee_jkm_borne_by_company' => true,
            'is_employee_jht_borne_by_company' => true,
            'is_employee_jp_borne_by_company' => true,
            'is_tax_borne_by_company' => false,
        ]);

        $result = $this->service->calculateForTemplateItem($item);

        // --- Validation ---

        // --- 1. Basic + Fixed ---
        // Spreadsheet Column Q (Gaji Pokok) + R (Tj. Tetap) = 5,315,000
        $this->assertEquals(5315000, $result['upah']);

        // --- 2. Non-Fixed ---
        // Total Non-Fixed (S+T+U+V) = 525,000 + 420,000 + 630,000 + 420,000 = 1,995,000
        $this->assertEquals(1995000, $result['total_non_fixed_allowances']);

        // --- 3. BPJS ---
        // JHT Employer (3.7% of 5,315,000) = 196,655
        // JHT Employee (2% of 5,315,000) = 106,300
        // Total JHT (Borne by Company) = 302,955
        $this->assertEquals(302955, $result['bpjs_employment']['details']['jht']['line_total']);

        // JKN Employer (4% of 5,315,000) = 212,600
        // JKN Employee (1% of 5,315,000) = 53,150
        // Total JKN (Borne by Company) = 265,750
        $this->assertEquals(265750, $result['bpjs_health']['total_total'] ?? $result['bpjs_health']['employer_total']);

        // --- 4. Tax (PPh 21) ---
        // Modernized Calculation (TER 2024):
        // Bruto = Upah (7,310,000) + Taxable BPJS Benefits (241,301 + 212,600) = 7,763,901
        // Category A, Rate 1.5% -> 116,458
        $this->assertEqualsWithDelta(116458, $result['pph21']['total'], 1.0);

        // --- 5. Total Direct Cost ---
        // Salary (7,310,000) + BPJS Total (756,856) + THR (442,916) + Comp (442,916) = 8,952,690
        $this->assertEqualsWithDelta(8952690, $result['total_direct_cost'], 10.0);
    }
}
