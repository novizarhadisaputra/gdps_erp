<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Enums\CostingCategory;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\Pages\ManageCostingTemplateItems;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\Item;
use Tests\TestCase;

class CostingTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_manage_costing_templates_via_lead(): void
    {
        $lead = Lead::factory()->create();
        $manageTemplatesPage = \Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ManageCostingTemplates::class;

        $this->actingAs(\App\Models\User::factory()->create());

        Livewire::test($manageTemplatesPage, ['record' => $lead->id])
            ->assertSuccessful()
            ->callTableAction('create', data: [
                'name' => 'Template for Lead',
                'pic_id' => auth()->id(),
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('costing_templates', [
            'lead_id' => $lead->id,
            'name' => 'Template for Lead',
        ]);
    }

    public function test_can_manage_items_via_nested_resource(): void
    {
        $lead = Lead::factory()->create();
        $template = CostingTemplate::factory()->create([
            'lead_id' => $lead->id,
            'pic_id' => auth()->id(),
        ]);
        $item = Item::factory()->create(['price' => 100000]);

        $this->actingAs(\App\Models\User::factory()->create());

        Livewire::test(ManageCostingTemplateItems::class, ['record' => $template->id])
            ->assertSuccessful()
            ->callTableAction('create', data: [
                'item_id' => $item->id,
                'name' => 'Nested Item',
                'category' => CostingCategory::MaterialConsumables->value,
                'quantity' => 2,
                'unit_price' => 100000,
                'depreciation_months' => 1,
                'markup_percent' => 10,
                'unit_price_markup' => 110000,
                'total_price' => 220000,
                'monthly_cost' => 220000,
            ])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('costing_template_items', [
            'costing_template_id' => $template->id,
            'name' => 'Nested Item',
            'quantity' => 2,
        ]);
    }
}
