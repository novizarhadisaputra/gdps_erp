<?php

namespace Modules\Logistics\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Logistics\Enums\PurchaseRequestStatus;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\ListPurchaseRequests;
use Modules\Logistics\Filament\Clusters\Logistics\Resources\PurchaseRequest\Pages\ViewPurchaseRequest;
use Modules\Logistics\Models\PurchaseRequest;
use Modules\MasterData\Models\ApprovalRule;
use Modules\MasterData\Models\Employee;
use Modules\MasterData\Models\Signature;
use Modules\Project\Models\Project;
use Tests\TestCase;

class PurchaseRequestResourceTest extends TestCase
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

        // Seed an employee for the user to be eligible for rules if needed
        Employee::factory()->create([
            'email' => $this->user->email,
        ]);
    }

    /** @test */
    public function it_can_render_the_list_page(): void
    {
        $this->actingAs($this->user);
        PurchaseRequest::factory()->count(3)->create();

        Livewire::test(ListPurchaseRequests::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_automatically_assigns_user_id_and_pr_number_on_creation(): void
    {
        $this->actingAs($this->user);
        $project = Project::factory()->create();
        $requester = Employee::factory()->create();

        $pr = PurchaseRequest::create([
            'project_id' => $project->id,
            'requester_id' => $requester->id,
            'total_amount' => 1000000,
            'status' => PurchaseRequestStatus::Draft,
        ]);

        $this->assertEquals($this->user->id, $pr->user_id);
        $this->assertNotNull($pr->pr_number);
        $this->assertStringStartsWith('PR/', $pr->pr_number);
    }

    /** @test */
    public function it_can_submit_for_approval(): void
    {
        $this->actingAs($this->user);
        $pr = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::Draft,
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ViewPurchaseRequest::class, ['record' => $pr->getRouteKey()])
            ->callAction('submit_for_approval')
            ->assertHasNoFormErrors();

        $this->assertEquals(PurchaseRequestStatus::Submitted, $pr->refresh()->status);
    }

    /** @test */
    public function it_can_approve_purchase_request_with_pin(): void
    {
        $this->actingAs($this->user);

        $pr = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::Submitted,
        ]);

        // Register an approval rule for the resource
        ApprovalRule::create([
            'resource_type' => PurchaseRequest::class,
            'approver_type' => 'User',
            'approver_user_id' => [$this->user->id],
            'order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(ViewPurchaseRequest::class, ['record' => $pr->getRouteKey()])
            ->callAction('approve_request', [
                'pin' => '123456',
            ])
            ->assertHasNoFormErrors();

        $this->assertEquals(PurchaseRequestStatus::Approved, $pr->refresh()->status);
        $this->assertDatabaseHas((new Signature)->getTable(), [
            'signable_id' => $pr->id,
            'signable_type' => PurchaseRequest::class,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_can_reject_purchase_request_with_reason(): void
    {
        $this->actingAs($this->user);

        $pr = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::Submitted,
        ]);

        // Register an approval rule
        ApprovalRule::create([
            'resource_type' => PurchaseRequest::class,
            'approver_type' => 'User',
            'approver_user_id' => [$this->user->id],
            'order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(ViewPurchaseRequest::class, ['record' => $pr->getRouteKey()])
            ->callAction('reject_request', [
                'reason' => 'Invalid budget',
            ])
            ->assertHasNoFormErrors();

        $this->assertEquals(PurchaseRequestStatus::Rejected, $pr->refresh()->status);
    }
}
