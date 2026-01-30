<?php

namespace Tests\Feature\Filament\Resources;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\CostingTemplateResource;
use Modules\MasterData\Filament\Resources\CostingTemplates\Pages\CreateCostingTemplate;
use Tests\TestCase;

class CostingTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_create_page()
    {
        $this->actingAs(\App\Models\User::factory()->create());
        $this->get(CostingTemplateResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_auto_fills_item_details_when_item_selected()
    {
        $this->actingAs(\App\Models\User::factory()->create());

        // Setup Data
        $group = \Modules\MasterData\Models\AssetGroup::factory()->create([
            'useful_life_years' => 4,
            'name' => 'IT Group',
            'type' => \Modules\MasterData\Enums\AssetGroupType::TangibleNonBuilding,
        ]);

        $category = \Modules\MasterData\Models\ItemCategory::factory()->create([
            'asset_group_id' => $group->id,
        ]);

        $uom = \Modules\MasterData\Models\UnitOfMeasure::factory()->create([
            'name' => 'Unit',
            'code' => 'UNIT-TEST',
        ]);

        try {
            $item = \Modules\MasterData\Models\Item::factory()->create([
                'item_category_id' => $category->id,
                'unit_of_measure_id' => $uom->id,
                'price' => 10000000,
                'name' => 'Laptop High Spec',
                'code' => 'TEST-ITEM-'.Str::uuid(),
            ]);
        } catch (\Exception $e) {
            dump($e->getMessage());
            throw $e;
        }

        // Interact
        $component = Livewire::test(CreateCostingTemplate::class);

        $uuid = Str::uuid()->toString();

        $component->fillForm([
            'costingTemplateItems' => [
                $uuid => ['item_id' => null],
            ],
        ]);

        $component->set("data.costingTemplateItems.{$uuid}.item_id", $item->id);

        $component->assertFormSet([
            "costingTemplateItems.{$uuid}.unit_price" => 10000000,
            "costingTemplateItems.{$uuid}.unit" => 'Unit',
            "costingTemplateItems.{$uuid}.useful_life_years" => 4,
        ]);
    }

    public function test_calculates_totals_and_monthly_cost()
    {
        $this->actingAs(\App\Models\User::factory()->create());

        $group = \Modules\MasterData\Models\AssetGroup::factory()->create(['useful_life_years' => 4, 'type' => \Modules\MasterData\Enums\AssetGroupType::TangibleNonBuilding]);

        // Ensure Item Category links to Asset Group
        $category = \Modules\MasterData\Models\ItemCategory::factory()->create([
            'asset_group_id' => $group->id,
        ]);

        $item = \Modules\MasterData\Models\Item::factory()->create([
            'item_category_id' => $category->id,
            'price' => 10000000,
            'code' => 'TEST-ITEM-CALC-'.Str::uuid(),
        ]);

        $uuid = Str::uuid()->toString();

        Livewire::test(CreateCostingTemplate::class)
            ->fillForm([
                'costingTemplateItems' => [
                    $uuid => [
                        'item_id' => $item->id,
                        'unit_price' => 10000000,
                        'quantity' => 1,
                        'markup_percent' => 0,
                        'unit' => 'Unit',
                        'category' => \Modules\MasterData\Enums\CostingCategory::ToolsEquipment->value,
                        'name' => 'Test Item',
                    ],
                ],
            ])
            ->set("data.costingTemplateItems.{$uuid}.markup_percent", 10)
            ->assertFormSet([
                "costingTemplateItems.{$uuid}.unit_price_markup" => 11000000,
            ])
            ->set("data.costingTemplateItems.{$uuid}.quantity", 2)
            ->assertFormSet([
                "costingTemplateItems.{$uuid}.total_price" => 22000000,
            ]);
    }
}
