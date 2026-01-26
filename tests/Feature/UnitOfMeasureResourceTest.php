<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\UnitsOfMeasure\Pages\ListUnitsOfMeasure;
use Modules\MasterData\Models\UnitOfMeasure;
use Tests\TestCase;

class UnitOfMeasureResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_units_of_measure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $uom = UnitOfMeasure::factory()->create([
            'name' => 'Test UOM',
        ]);

        Livewire::test(ListUnitsOfMeasure::class)
            ->assertCanSeeTableRecords([$uom])
            ->assertSee('Test UOM');
    }
}
