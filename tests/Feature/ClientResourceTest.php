<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\Clients\Pages\ListClients;
use Modules\MasterData\Models\Client;
use Tests\TestCase;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    /**
     * Test that the client resource list page can be rendered.
     */
    public function test_can_list_clients(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client = Client::factory()->create([
            'name' => 'Test Client',
        ]);

        Livewire::test(ListClients::class)
            ->assertCanSeeTableRecords([$client])
            ->assertSee('Test Client');
    }

    /**
     * Test that we can search for a client in the list.
     */
    public function test_can_search_clients(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $client1 = Client::factory()->create(['name' => 'Searchable Client']);
        $client2 = Client::factory()->create(['name' => 'Hidden Client']);

        Livewire::test(ListClients::class)
            ->searchTable('Searchable Client')
            ->assertCanSeeTableRecords([$client1])
            ->assertCanNotSeeTableRecords([$client2]);
    }
}
