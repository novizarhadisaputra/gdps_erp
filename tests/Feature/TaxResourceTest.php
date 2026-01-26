<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\Taxes\Pages\ListTaxes;
use Modules\MasterData\Models\Tax;
use Tests\TestCase;

class TaxResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_taxes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tax = Tax::factory()->create([
            'name' => 'Test Tax',
        ]);

        Livewire::test(ListTaxes::class)
            ->assertCanSeeTableRecords([$tax])
            ->assertSee('Test Tax');
    }
}
