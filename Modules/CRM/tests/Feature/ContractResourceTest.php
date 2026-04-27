<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\Contract\Pages\ListContracts;
use Modules\CRM\Models\Contract;
use Modules\CRM\Models\Lead;
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
        $roleId = (string) Str::uuid();
        \Illuminate\Support\Facades\DB::table('roles')->insert([
            'id' => $roleId,
            'name' => 'super_admin',
            'guard_name' => 'web',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $user = User::factory()->create();
        $user->assignRole('super_admin');
        $this->actingAs($user);

        $lead = Lead::factory()->create();

        $contract = Contract::factory()->create([
            'lead_id' => $lead->id,
            'number' => 'CONT-001',
        ]);

        Livewire::test(ListContracts::class, [
            'parentRecord' => $lead,
        ])
            ->assertCanSeeTableRecords([$contract])
            ->assertSee('CONT-001');
    }
}
