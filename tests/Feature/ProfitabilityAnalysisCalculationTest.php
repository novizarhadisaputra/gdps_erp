<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ProfitabilityAnalysis\Pages\CreateProfitabilityAnalysis;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\DirectCostCategory;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Tests\TestCase;

class ProfitabilityAnalysisCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);

        DirectCostCategory::firstOrCreate(['code' => 'tools_equipment'], [
            'name' => 'Tools & Equipment',
            'type' => 'direct',
        ]);
    }

    public function test_pa_totals_update_when_operational_item_added(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create();
        $projectArea = ProjectArea::factory()->create();
        $lead = Lead::factory()->create([
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
        ]);

        $gi = GeneralInformation::factory()->create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'project_area_id' => $projectArea->id,
            'estimated_start_date' => now(),
            'estimated_end_date' => now()->addMonths(12),
        ]);

        $item = Item::factory()->create(['name' => 'Laptop', 'price' => 1000000]);
        $toolsCat = DirectCostCategory::where('code', 'tools_equipment')->first();

        // Mock the route for InteractsWithParentRecord
        $route = new \Illuminate\Routing\Route(['GET', 'HEAD'], 'crm/leads/{lead}/profitability-analysis/create', [
            'as' => 'filament.admin.crm.resources.leads.resources.profitability-analysis.pages.create-profitability-analysis',
        ]);
        $route->bind(request());
        $route->setParameter('lead', (string) $lead->id);
        request()->setRouteResolver(fn () => $route);

        $test = Livewire::test(CreateProfitabilityAnalysis::class, [
            'lead' => $lead->id,
            'parentRecord' => $lead,
        ])
            ->fillForm([
                'general_information_id' => $gi->id,
                'customer_id' => $customer->id,
                'project_area_id' => $projectArea->id,
                'product_cluster_id' => ProductCluster::factory()->create()->id,
                'work_scheme_id' => WorkScheme::factory()->create()->id,
                'tax_id' => Tax::factory()->create()->id,
                'year' => date('Y'),
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(12)->format('Y-m-d'),
                'management_fee_rate' => 0,
            ])
            ->set('data.operationalItems', [
                [
                    'costable_type' => Item::class,
                    'costable_id' => $item->id,
                    'direct_cost_category_id' => $toolsCat->id,
                    'unit_cost_price' => 1000000,
                    'quantity' => 5,
                    'duration_months' => 12,
                    'markup_percentage' => 0,
                    'depreciation_months' => 1,
                ],
            ]);

        $test->assertSet('data.direct_cost_tools', 5000000);
    }
}
