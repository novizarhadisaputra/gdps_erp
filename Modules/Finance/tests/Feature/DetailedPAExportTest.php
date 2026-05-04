<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Models\Customer;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class DetailedPAExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_detailed_pa_export()
    {
        Excel::fake();

        $user = User::factory()->create();
        $customer = Customer::factory()->create();
        $area = ProjectArea::factory()->create();

        $pa = ProfitabilityAnalysis::create([
            'customer_id' => $customer->id,
            'project_area_id' => $area->id,
            'year' => date('Y'),
            'status' => 'draft',
            'is_manual_cost' => false,
            'duration_months' => 12,
            'start_date' => now(),
            'end_date' => now()->addYear(),
        ]);

        $jobPosition = JobPosition::factory()->create();

        $pa->manpowerItems()->create([
            'costable_id' => $jobPosition->id,
            'costable_type' => JobPosition::class,
            'quantity' => 1,
            'unit_cost_price' => 5000000,
            'multiplier' => 1,
            'total_cost' => 5000000,
            'markup_percentage' => 10,
            'total_monthly_sale' => 5500000,
        ]);

        // We focus on testing the export class itself

        // We can't easily test the Filament action trigger here without Livewire test
        // But we can test the export class itself

        $export = new \Modules\Finance\Exports\ProfitabilityAnalysisExport($pa);
        $view = $export->view();

        $this->assertEquals('finance::exports.profitability-analysis-excel', $view->name());
        $data = $view->getData()['data'];

        $this->assertArrayHasKey('header', $data);
        $this->assertArrayHasKey('revenue', $data);
        $this->assertArrayHasKey('direct_cost', $data);
        $this->assertArrayHasKey('gp', $data);
        $this->assertArrayHasKey('indirect_cost', $data);
        $this->assertArrayHasKey('financial', $data);

        $this->assertArrayHasKey('base', $data['direct_cost']['manpower']);
        $this->assertArrayHasKey('benefit', $data['direct_cost']['manpower']);
        $this->assertEquals('Manpower', $data['direct_cost']['manpower']['base']['name']);

        // Check financial calculations
        $this->assertArrayHasKey('interest', $data['financial']);
        $this->assertArrayHasKey('tax', $data['financial']);
        $this->assertArrayHasKey('net_profit', $data['financial']);
    }
}
