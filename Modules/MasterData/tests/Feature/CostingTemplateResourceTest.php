<?php

namespace Modules\MasterData\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\MasterData\Enums\CostingCategory;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\CostingTemplates\CostingTemplateResource;
use Modules\MasterData\Models\AssetGroup;
use Modules\MasterData\Models\CostingTemplate;
use Modules\MasterData\Models\Item;
use Tests\TestCase;
use Tests\Traits\TestsFilamentResource;

class CostingTemplateResourceTest extends TestCase
{
    use RefreshDatabase, TestsFilamentResource;

    protected function getResource(): string
    {
        return CostingTemplateResource::class;
    }

    protected function getValidInput(?Model $record = null): array
    {
        $item = Item::factory()->create(['price' => 50000]);
        $uuid = (string) Str::uuid();

        return [
            'name' => 'Test Costing Template',
            'description' => 'Test Description',
            'costingTemplateItems' => [
                $uuid => [
                    'item_id' => $item->id,
                    'name' => 'Test Item',
                    'category' => CostingCategory::MaterialConsumables->value,
                    'quantity' => 1,
                    'unit_price' => 50000,
                    'markup_percent' => 0,
                    'unit_price_markup' => 50000,
                    'total_price' => 50000,
                    'useful_life_years' => 0,
                    'monthly_cost' => 50000,
                ],
            ],
        ];
    }

    public function test_calculations_update_live()
    {
        // Setup data
        $assetGroup = AssetGroup::factory()->create(['useful_life_years' => 5]);
        $item = Item::factory()->create([
            'price' => 100000,
        ]);

        $record = CostingTemplate::factory()->create(['name' => 'Original Name']);

        $component = Livewire::test(CostingTemplateResource::getPages()['index']->getPage())
            ->mountTableAction('edit', $record);

        // Get the UUID of the first item from the existing state
        $data = $component->get('mountedTableActions.0.data');
        $uuid = array_key_first($data['costingTemplateItems'] ?? []);

        if (! $uuid) {
            $uuid = (string) \Illuminate\Support\Str::uuid();
        }

        $component->fillForm([
            'name' => 'Calculation Updated',
            'costingTemplateItems' => [
                $uuid => [
                    'item_id' => $item->id,
                    'name' => 'Calc Item',
                    'category' => CostingCategory::MaterialConsumables->value,
                    'quantity' => 2,
                    'unit_price' => 100000,
                    'markup_percent' => 10,
                    'unit_price_markup' => 110000,
                    'total_price' => 220000,
                    'useful_life_years' => 5,
                    'monthly_cost' => 3666.67,
                ],
            ],
        ])
            ->callMountedTableAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('costing_templates', [
            'id' => $record->id,
            'name' => 'Calculation Updated',
        ]);

        $this->assertDatabaseHas('costing_template_items', [
            'costing_template_id' => $record->id,
            'unit_price' => 100000,
            'total_price' => 220000,
            'monthly_cost' => 3666.67,
        ]);
    }

    public function test_can_create_record(): void
    {
        $item = Item::factory()->create();

        $component = Livewire::test(CostingTemplateResource::getPages()['index']->getPage())
            ->mountAction('create');

        // Get the UUID of the initial item added by defaultItems(1)
        $data = $component->get('mountedActions.0.data');
        $uuid = array_key_first($data['costingTemplateItems'] ?? []);

        if (! $uuid) {
            $uuid = (string) Str::uuid();
        }

        $component->fillForm([
            'name' => 'New Template',
            'costingTemplateItems' => [
                $uuid => [
                    'item_id' => $item->id,
                    'name' => 'New Item',
                    'category' => CostingCategory::MaterialConsumables->value,
                    'quantity' => 1,
                    'unit_price' => 50000,
                ],
            ],
        ])
            ->callMountedAction()
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('costing_templates', ['name' => 'New Template']);
    }
}
