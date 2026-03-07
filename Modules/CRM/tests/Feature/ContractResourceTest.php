<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\ListContracts;
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
        $roleId = (string) \Illuminate\Support\Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'super_admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = \App\Models\User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $contract = Contract::factory()->create([
            'contract_number' => 'CONT-001',
        ]);

        Livewire::test(ListContracts::class)
            ->assertCanSeeTableRecords([$contract])
            ->assertSee('CONT-001');
    }
}
