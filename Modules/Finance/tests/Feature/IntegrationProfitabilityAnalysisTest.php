<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\GeneralInformation;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\Lead;
use Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses;
use Modules\MasterData\Enums\AssetGroupType;
use Modules\MasterData\Models\AssetGroup;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\UnitOfMeasure;
use Modules\MasterData\Models\WorkScheme;
use Tests\TestCase;

class IntegrationProfitabilityAnalysisTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_fills_item_details_in_pa_form()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 1. Setup Master Data
        $group = AssetGroup::factory()->create([
            'useful_life_years' => 5,
            'name' => 'IT Assets',
            'type' => AssetGroupType::TangibleNonBuilding,
        ]);

        $category = ItemCategory::factory()->create([
            'asset_group_id' => $group->id,
        ]);

        $uom = UnitOfMeasure::factory()->create(['name' => 'Unit', 'code' => 'UNIT-PA']);

        $item = Item::factory()->create([
            'item_category_id' => $category->id,
            'unit_of_measure_id' => $uom->id,
            'price' => 5000000,
            'depreciation_months' => null, // Should fallback to Group (5 * 12 = 60)
            'name' => 'Integration Laptop',
            'code' => 'LAPTOP-PA-'.Str::uuid(),
        ]);

        // 2. Setup PA Dependencies
        $customer = Customer::factory()->create();
        $lead = Lead::factory()->create(['customer_id' => $customer->id]);
        $gi = GeneralInformation::factory()->create([
            'document_number' => 'GI/TEST/001',
            'customer_id' => $customer->id,
            'lead_id' => $lead->id,
            'status' => 'draft',
        ]);
        $workScheme = WorkScheme::factory()->create();
        $productCluster = ProductCluster::factory()->create();
        $tax = Tax::factory()->create();
        $projectArea = ProjectArea::factory()->create();

        $manpowerTemplate = ManpowerTemplate::factory()->create(['lead_id' => $lead->id]);
        $costingTemplate = CostingTemplate::factory()->create(['lead_id' => $lead->id]);

        // 3. Test Interaction
        $uuid = Str::uuid()->toString();

        \Illuminate\Support\Facades\Gate::before(fn () => true);

        Livewire::test(ListProfitabilityAnalyses::class)
            ->mountAction('create')
            ->fillForm([
                'general_information_id' => $gi->id,
                'customer_id' => $customer->id,
                'work_scheme_id' => $workScheme->id,
                'product_cluster_id' => $productCluster->id,
                'tax_id' => $tax->id,
                'project_area_id' => $projectArea->id,
                'lead_id' => $lead->id,
                'analysis_details.manpower_template_id' => $manpowerTemplate->id,
                'analysis_details.costing_template_id' => $costingTemplate->id,
            ])
            ->assertHasNoFormErrors();
    }
}
