<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Resources\Contracts\Pages\ListContracts;
use Modules\CRM\Models\Contract;
use Tests\TestCase;

class ContractResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_contracts(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $contract = Contract::factory()->create([
            'contract_number' => 'CONT-001',
        ]);

        Livewire::test(ListContracts::class)
            ->assertCanSeeTableRecords([$contract])
            ->assertSee('CONT-001');
    }
}
