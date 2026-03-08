<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\Item;
use Tests\TestCase;

class CostingTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

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
                'file' => [\Illuminate\Http\UploadedFile::fake()->create('document.pdf')],
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
        \Modules\CRM\Models\CostingTemplateItem::factory()->create([
            'costing_template_id' => $template->id,
            'name' => 'Nested Item',
            'quantity' => 2,
        ]);

        $this->actingAs(\App\Models\User::factory()->create());

        $this->get(\Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\CostingTemplate\CostingTemplateResource::getUrl('items', ['lead' => $lead->id, 'record' => $template->id]))
            ->assertSuccessful()
            ->assertSee('Tools & Equipment Costing');

        $this->assertDatabaseHas('costing_template_items', [
            'costing_template_id' => $template->id,
            'name' => 'Nested Item',
            'quantity' => 2,
        ]);
    }
}
