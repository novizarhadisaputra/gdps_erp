<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Customers\Pages\ListCustomers;
use Modules\CRM\Models\Customer;
use Tests\TestCase;

class CustomerResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    /**
     * Test that the customer resource list page can be rendered.
     */
    public function test_can_list_customers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer = Customer::factory()->create([
            'name' => 'Test Customer',
        ]);

        Livewire::test(ListCustomers::class)
            ->assertCanSeeTableRecords([$customer])
            ->assertSee('Test Customer');
    }

    /**
     * Test that we can search for a customer in the list.
     */
    public function test_can_search_customers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $customer1 = Customer::factory()->create(['name' => 'Searchable Customer']);
        $customer2 = Customer::factory()->create(['name' => 'Hidden Customer']);

        Livewire::test(ListCustomers::class)
            ->searchTable('Searchable Customer')
            ->assertCanSeeTableRecords([$customer1])
            ->assertCanNotSeeTableRecords([$customer2]);
    }
}
