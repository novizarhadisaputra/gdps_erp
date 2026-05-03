<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\SalesOrders\Pages\ListSalesOrders;
use Modules\CRM\Models\SalesOrder;
use Tests\TestCase;

class SalesOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_sales_order_list_page()
    {
        $user = User::factory()->create();

        $project = \Modules\Project\Models\Project::factory()->create();

        SalesOrder::factory()->count(3)->create([
            'project_id' => $project->id,
            'amount' => 1000,
        ]);

        $this->actingAs($user);

        Livewire::test(ListSalesOrders::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(SalesOrder::all())
            ->assertSee('Total Amount'); // Verify column header
    }
}
