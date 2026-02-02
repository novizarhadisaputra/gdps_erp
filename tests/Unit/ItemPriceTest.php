<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemPriceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_gets_correct_price_for_area()
    {
        $item = \Modules\MasterData\Models\Item::factory()->create(['price' => 1000]);
        $area = \Modules\MasterData\Models\ProjectArea::factory()->create();

        // No area price -> returns default
        $this->assertEquals(1000, $item->getPriceForArea($area->id));

        // Add area price
        \Modules\MasterData\Models\ItemPrice::create([
            'item_id' => $item->id,
            'project_area_id' => $area->id,
            'price' => 2000,
        ]);

        $item->refresh();

        // Has area price -> returns area price
        $this->assertEquals(2000, $item->getPriceForArea($area->id));

        // Different area -> returns default
        $otherArea = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $this->assertEquals(1000, $item->getPriceForArea($otherArea->id));
    }
}
