<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\ManpowerTemplateItem;
use Modules\MasterData\Models\BpjsBasisType;
use Modules\MasterData\Models\BpjsHealthConfig;
use Modules\MasterData\Models\BpjsJhtConfig;
use Modules\MasterData\Models\BpjsJkkConfig;
use Modules\MasterData\Models\BpjsJkmConfig;
use Modules\MasterData\Models\BpjsJpConfig;
use Modules\MasterData\Models\ContractType;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\TaxPtkpConfig;
use Modules\MasterData\Models\TaxTerRate;
use Modules\MasterData\Models\WorkScheme;
use Tests\TestCase;

class ManpowerTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create();
        $this->seedMasterData();
    }

    protected function seedMasterData(): void
    {
        // 1. PTKP Configs
        TaxPtkpConfig::create(['code' => 'TK/0', 'name' => 'TK/0', 'annual_amount' => 54000000, 'tax_category' => 'A', 'is_active' => true]);
        TaxPtkpConfig::create(['code' => 'K/3', 'name' => 'K/3', 'annual_amount' => 72000000, 'tax_category' => 'C', 'is_active' => true]);

        // 2. BPJS Health Config
        BpjsHealthConfig::create([
            'name' => 'BPJS Kesehatan PPU',
            'employee_type' => 'ppu',
            'employer_rate' => 0.04,
            'employee_rate' => 0.01,
            'cap_nominal' => 12000000,
            'is_active' => true,
        ]);

        // 3. BPJS Employment Configs
        BpjsJkkConfig::create(['name' => 'JKK Very Low', 'employee_type' => 'ppu', 'risk_level' => 'very_low', 'employer_rate' => 0.0024, 'employee_rate' => 0, 'is_active' => true]);
        BpjsJkmConfig::create(['name' => 'JKM PPU', 'employee_type' => 'ppu', 'employer_rate' => 0.003, 'employee_rate' => 0, 'is_active' => true]);
        BpjsJhtConfig::create(['name' => 'JHT PPU', 'employee_type' => 'ppu', 'employer_rate' => 0.037, 'employee_rate' => 0.02, 'is_active' => true]);
        BpjsJpConfig::create(['name' => 'JP PPU', 'employee_type' => 'ppu', 'employer_rate' => 0.02, 'employee_rate' => 0.01, 'cap_nominal' => 11086300, 'is_active' => true]);

        // 4. Tax TER Rates (Simplified for testing)
        TaxTerRate::create(['type' => 'ter', 'category' => 'A', 'min_gross' => 0, 'max_gross' => 5400000, 'rate' => 0, 'is_active' => true]);
        TaxTerRate::create(['type' => 'ter', 'category' => 'A', 'min_gross' => 5400001, 'max_gross' => 50000000, 'rate' => 2, 'is_active' => true]);
        TaxTerRate::create(['type' => 'ter', 'category' => 'C', 'min_gross' => 5400001, 'max_gross' => 50000000, 'rate' => 0.5, 'is_active' => true]);
    }

    /** @test */
    public function it_calculates_cost_simulation_with_accurate_results(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        MinimumWage::create([
            'project_area_id' => $area->id,
            'year' => (int) date('Y'),
            'amount' => 5000000,
            'province' => 'Test Province',
            'is_active' => true,
        ]);

        $jobPosition = JobPosition::factory()->create();
        $productCluster = ProductCluster::factory()->create();
        $workScheme = WorkScheme::factory()->create();

        $template = ManpowerTemplate::factory()->create([
            'year' => (int) date('Y'),
        ]);

        // Scenario 1: Basic salary = 5,000,000 (UMK)
        // BPJS Health ER: 4% of 5M = 200,000
        // BPJS JKK ER: 0.24% of 5M = 12,000
        // BPJS JKM ER: 0.3% of 5M = 15,000
        // BPJS JHT ER: 3.7% of 5M = 185,000
        // BPJS JP ER: 2% of 5M = 100,000
        // Total BPJS ER: 512,000
        // Accruals (THR + Comp): (5M/12) + (5M/12) = 416,666.67 + 416,666.67 = 833,333.33
        // Total Direct Cost: 5,000,000 + 512,000 + 833,333.33 = 6,345,333.33 (approx)

        ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => $jobPosition->id,
            'quantity' => 1,
            'basic_salary' => 5000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => $workScheme->id,
            'ptkp_status' => 'TK/0',
            'is_bpjs_active' => true,
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
        ]);

        $simulation = $template->getCostSimulation();

        $this->assertEquals(5000000, $simulation['rows'][0]['upah']);
        $this->assertEquals(512000, $simulation['rows'][0]['bpjs_total']);
        $this->assertGreaterThan(6345000, $simulation['total']);
    }

    /** @test */
    public function it_applies_bpjs_caps_for_high_salaries(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        $template = ManpowerTemplate::factory()->create();
        $productCluster = ProductCluster::factory()->create();

        // Salary 20,000,000 (Above caps)
        // Health Cap: 12,000,000 -> ER: 4% of 12M = 480,000
        // JP Cap: 11,086,300 -> ER: 2% of 11.08M = 221,726
        // JKK/JKM/JHT (No cap): 20M * (0.24% + 0.3% + 3.7%) = 20M * 4.24% = 848,000
        // Expected BPJS ER Total: 480,000 + 221,726 + 848,000 = 1,549,726

        ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 20000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'ptkp_status' => 'TK/0',
            'is_bpjs_active' => true,
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
        ]);

        $simulation = $template->getCostSimulation();
        $this->assertEquals(1549726, round($simulation['rows'][0]['bpjs_total']));
    }

    /** @test */
    public function it_applies_future_adjustment_scaling_correctly(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        $template = ManpowerTemplate::factory()->create();
        $productCluster = ProductCluster::factory()->create();

        // Salary 10,000,000, Scaling 10%
        ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 10000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'future_adjustment_rate' => 10, // 10%
        ]);

        $simulation = $template->getCostSimulation();

        // Without scale: Total Direct Cost
        // Let's assume some base value. If scale is 10%, result should be 1.1 * base.
        $baseRes = app(\Modules\Finance\Services\ManpowerCostingService::class)->calculate(
            basicSalary: 10000000,
            allowances: [],
            projectAreaId: $area->id,
            year: (int) date('Y')
        );

        $expectedUnitCost = $baseRes['total_direct_cost'] * 1.1;

        $this->assertEquals(round($expectedUnitCost), round($simulation['rows'][0]['unit_cost']));
    }

    /** @test */
    public function it_renders_manpower_template_create_page(): void
    {
        $this->actingAs($this->user);

        $lead = Lead::factory()->create();
        $area = ProjectArea::factory()->create();
        $area->customers()->attach($lead->customer_id);

        $response = $this->get(ManpowerTemplateResource::getUrl('create', ['lead' => $lead]));

        $response->assertSuccessful();
        $response->assertSee('Costing Identification');
    }

    /** @test */
    public function it_correctly_calculates_tax_relief_differences(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        $template = ManpowerTemplate::factory()->create();
        $productCluster = ProductCluster::factory()->create();

        // K/3 has higher relief, but in TER method (2024), it uses different categories (C instead of A)
        // Gross: 9,000,000
        // Category A (TK/0): Rate 2.0% (example per seed) -> 180,000
        // Category C (K/3): Rate might be lower or 0 at this level.
        // Let's seed C rate at 0.5%
        TaxTerRate::create(['category' => 'C', 'min_gross' => 5400001, 'max_gross' => 10000000, 'rate' => 0.5, 'is_active' => true]);

        $itemTk0 = ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 9000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'ptkp_status' => 'TK/0',
            'use_ter_method' => true,
        ]);

        $itemK3 = ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 9000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'ptkp_status' => 'K/3',
            'use_ter_method' => true,
        ]);

        $simulation = $template->getCostSimulation();

        $taxTk0 = $simulation['rows'][0]['pph21']['total'];
        $taxK3 = $simulation['rows'][1]['pph21']['total'];

        $this->assertGreaterThan($taxK3, $taxTk0);
    }

    /** @test */
    public function it_applies_contract_type_rules_to_calculations(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        $template = ManpowerTemplate::factory()->create();
        $productCluster = ProductCluster::factory()->create();

        $pkwt = ContractType::create(['code' => 'PKWT', 'name' => 'PKWT', 'is_active' => true]);
        $pkwtt = ContractType::create(['code' => 'PKWTT', 'name' => 'PKWTT', 'is_active' => true]);
        $mitra = ContractType::create(['code' => 'MITRA', 'name' => 'MITRA', 'is_active' => true]);

        // Scenario: If MITRA contract type is chosen, compensation accruals are not billed
        $itemMitra = ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 5000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'contract_type_id' => $mitra->id,
        ]);

        $simulation = $template->getCostSimulation();

        // MITRA has no compensation accruals
        $this->assertEquals(0, $simulation['rows'][0]['accruals']['compensation']);
    }

    /** @test */
    public function it_supports_split_bpjs_kesehatan_and_ketenagakerjaan_basis_types(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        MinimumWage::create([
            'project_area_id' => $area->id,
            'year' => (int) date('Y'),
            'amount' => 5000000,
            'province' => 'Test Province',
            'is_active' => true,
        ]);

        $template = ManpowerTemplate::factory()->create([
            'year' => (int) date('Y'),
        ]);

        $productCluster = ProductCluster::factory()->create();

        // Seed split BPJS Basis Types
        $basisGP = BpjsBasisType::create([
            'code' => 'GP',
            'name' => 'Gaji Pokok',
            'formula_code' => 'gaji_pokok',
            'is_active' => true,
        ]);

        $basisGPF = BpjsBasisType::create([
            'code' => 'GPF',
            'name' => 'Gaji + Tunjangan Tetap',
            'formula_code' => 'gaji_plus_tunjangan_tetap',
            'is_active' => true,
        ]);

        // Scenario: GP = 5M, Fixed Allowance = 1M
        // bpjs_kesehatan_basis_id = GP (5M basis)
        // bpjs_ketenagakerjaan_basis_id = GPF (6M basis)
        $item = ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 5000000,
            'allowances' => [
                ['name' => 'Fixed Allowance', 'amount' => 1000000, 'type' => 'fixed', 'is_active' => true],
            ],
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'bpjs_kesehatan_basis_id' => $basisGP->id,
            'bpjs_ketenagakerjaan_basis_id' => $basisGPF->id,
            'is_bpjs_active' => true,
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
        ]);

        $simulation = $template->getCostSimulation();

        // BPJS Health ER: 4% of 5M = 200,000
        // BPJS Employment ER: 6.24% of 6M (5M GP + 1M Fixed) = 374,400
        // Total BPJS ER = 574,400
        $this->assertEquals(200000, $simulation['rows'][0]['bpjs_health']['employer']);
        $this->assertEquals(374400, $simulation['rows'][0]['bpjs_employment']['employer_total']);
        $this->assertEquals(574400, $simulation['rows'][0]['bpjs_total']);
    }

    /** @test */
    public function it_applies_custom_bpjs_configs_if_specified(): void
    {
        $this->actingAs($this->user);

        $area = ProjectArea::factory()->create();
        MinimumWage::create([
            'project_area_id' => $area->id,
            'year' => (int) date('Y'),
            'amount' => 5000000,
            'province' => 'Test Province',
            'is_active' => true,
        ]);

        $template = ManpowerTemplate::factory()->create([
            'year' => (int) date('Y'),
        ]);

        $productCluster = ProductCluster::factory()->create();

        // Create custom BPJS configs
        $customHealth = BpjsHealthConfig::create([
            'name' => 'Custom Health Rate',
            'employee_type' => 'ppu',
            'employer_rate' => 0.05, // 5% instead of 4%
            'employee_rate' => 0.02,
            'cap_nominal' => 15000000,
            'is_active' => true,
        ]);

        $customJkk = BpjsJkkConfig::create([
            'name' => 'Custom JKK Rate',
            'employee_type' => 'ppu',
            'risk_level' => 'very_low',
            'employer_rate' => 0.01, // 1% instead of 0.24%
            'employee_rate' => 0,
            'is_active' => true,
        ]);

        $customJkm = BpjsJkmConfig::create([
            'name' => 'Custom JKM Rate',
            'employee_type' => 'ppu',
            'employer_rate' => 0.005, // 0.5% instead of 0.3%
            'employee_rate' => 0,
            'is_active' => true,
        ]);

        $customJht = BpjsJhtConfig::create([
            'name' => 'Custom JHT Rate',
            'employee_type' => 'ppu',
            'employer_rate' => 0.04, // 4% instead of 3.7%
            'employee_rate' => 0.02,
            'is_active' => true,
        ]);

        $customJp = BpjsJpConfig::create([
            'name' => 'Custom JP Rate',
            'employee_type' => 'ppu',
            'employer_rate' => 0.03, // 3% instead of 2%
            'employee_rate' => 0.01,
            'cap_nominal' => 12000000,
            'is_active' => true,
        ]);

        $item = ManpowerTemplateItem::create([
            'manpower_template_id' => $template->id,
            'product_cluster_id' => $productCluster->id,
            'job_position_id' => JobPosition::factory()->create()->id,
            'quantity' => 1,
            'basic_salary' => 5000000,
            'project_area_id' => $area->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'bpjs_health_config_id' => $customHealth->id,
            'bpjs_jkk_config_id' => $customJkk->id,
            'bpjs_jkm_config_id' => $customJkm->id,
            'bpjs_jht_config_id' => $customJht->id,
            'bpjs_jp_config_id' => $customJp->id,
            'is_bpjs_active' => true,
        ]);

        $simulation = $template->getCostSimulation();

        // BPJS Health Custom ER: 5% of 5M = 250,000
        // BPJS JKK Custom ER: 1% of 5M = 50,000
        // BPJS JKM Custom ER: 0.5% of 5M = 25,000
        // BPJS JHT Custom ER: 4% of 5M = 200,000
        // BPJS JP Custom ER: 3% of 5M = 150,000
        $this->assertEquals(250000, $simulation['rows'][0]['bpjs_health']['employer']);
        $this->assertEquals(425000, $simulation['rows'][0]['bpjs_employment']['employer_total']);
        $this->assertEquals(675000, $simulation['rows'][0]['bpjs_total']);
    }
}
