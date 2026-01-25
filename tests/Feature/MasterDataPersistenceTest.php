<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Item;
use Modules\MasterData\Models\ItemCategory;
use Modules\MasterData\Models\UnitOfMeasure;
use Tests\TestCase;

class MasterDataPersistenceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_auto_generates_code_for_item_category()
    {
        $category = ItemCategory::create([
            'name' => 'General Supplies Category', // Unique name
            'description' => 'Various supplies',
        ]);

        $this->assertNotEmpty($category->code);
        $this->assertEquals('GSY', $category->code); // General (G) + Supplies (S) + Category (y)
    }

    /** @test */
    public function it_auto_generates_code_for_item()
    {
        $category = ItemCategory::create(['name' => 'Tools Hardware']);
        $uom = UnitOfMeasure::create(['name' => 'Piece Unit', 'code' => 'PUN']);

        $item = Item::create([
            'item_category_id' => $category->id,
            'unit_of_measure_id' => $uom->id,
            'name' => 'Dell Laptop Latitude',
            'description' => 'Dell Latitude',
        ]);

        $this->assertNotEmpty($item->code);
        // Dell (D) + Laptop (L) + Latitude (e) = DLE
        $this->assertEquals('DLE', $item->code);
    }

    /** @test */
    public function it_auto_generates_code_for_employee()
    {
        $employee = Employee::create([
            'name' => 'Jane Smith Doe',
            'email' => 'jane@example.com',
            'status' => 'active',
        ]);

        $this->assertNotEmpty($employee->code);
        // Jane (J) + Smith (S) + Doe (e) = JSE
        $this->assertEquals('JSE', $employee->code);
    }
}
