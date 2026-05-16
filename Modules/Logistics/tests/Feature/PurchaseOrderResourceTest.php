<?php

namespace Modules\Logistics\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Logistics\Enums\PurchaseOrderStatus;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\ListPurchaseOrders;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseOrder\Pages\ViewPurchaseOrder;
use Modules\Logistics\Models\PurchaseOrder;
use Modules\Logistics\Models\Warehouse;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Vendor;
use Modules\Project\Models\Project;
use Tests\TestCase;

class PurchaseOrderResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create(['signature_pin' => '123456']);

        // Load necessary migrations from other modules
        $this->artisan('migrate', [
            '--path' => 'Modules/MasterData/database/migrations',
            '--realpath' => true,
        ]);
        Employee::factory()->create([
            'email' => $this->user->email,
        ]);
    }

    /** @test */
    public function it_can_render_the_list_page(): void
    {
        $this->actingAs($this->user);
        PurchaseOrder::factory()->count(3)->create();

        Livewire::test(ListPurchaseOrders::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_automatically_assigns_user_id_and_po_number_on_creation(): void
    {
        $this->actingAs($this->user);
        $project = Project::factory()->create();
        $vendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $po = PurchaseOrder::create([
            'project_id' => $project->id,
            'vendor_id' => $vendor->id,
            'warehouse_id' => $warehouse->id,
            'total_amount' => 1000000,
            'status' => PurchaseOrderStatus::Draft,
        ]);

        $this->assertEquals($this->user->id, $po->user_id);
        $this->assertNotNull($po->po_number);
        $this->assertStringStartsWith('PO/', $po->po_number);
    }

    /** @test */
    public function it_can_submit_for_approval(): void
    {
        $this->actingAs($this->user);
        $po = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::Draft,
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ViewPurchaseOrder::class, ['record' => $po->getRouteKey()])
            ->callAction('submit_for_approval')
            ->assertHasNoActionErrors();

        $this->assertEquals(PurchaseOrderStatus::Submitted, $po->refresh()->status);
    }

    /** @test */
    public function it_can_approve_purchase_order_with_pin(): void
    {
        $this->actingAs($this->user);

        $po = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::Submitted,
        ]);

        // Register an approval rule
        ApprovalRule::create([
            'resource_type' => PurchaseOrder::class,
            'approver_type' => 'User',
            'approver_user_id' => [$this->user->id],
            'order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(ViewPurchaseOrder::class, ['record' => $po->getRouteKey()])
            ->callAction('approve_order', [
                'pin' => '123456',
            ])
            ->assertHasNoActionErrors();

        $this->assertEquals(PurchaseOrderStatus::Approved, $po->refresh()->status);
    }

    /** @test */
    public function it_can_mark_as_sent(): void
    {
        $this->actingAs($this->user);
        $po = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::Approved,
        ]);

        Livewire::test(ViewPurchaseOrder::class, ['record' => $po->getRouteKey()])
            ->callAction('mark_as_sent')
            ->assertHasNoActionErrors();

        $this->assertEquals(PurchaseOrderStatus::Sent, $po->refresh()->status);
    }

    /** @test */
    public function it_can_mark_as_completed(): void
    {
        $this->actingAs($this->user);
        $po = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::Sent,
        ]);

        Livewire::test(ViewPurchaseOrder::class, ['record' => $po->getRouteKey()])
            ->callAction('mark_as_completed')
            ->assertHasNoActionErrors();

        $this->assertEquals(PurchaseOrderStatus::Completed, $po->refresh()->status);
    }
}
