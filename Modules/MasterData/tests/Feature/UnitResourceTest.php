<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\Units\Pages\ListUnits;
use Modules\MasterData\Services\UnitService;
use Tests\TestCase;

class UnitResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
        UnitService::clearCache();
    }

    /**
     * Test that the unit resource list page can be rendered and shows data from the API.
     */
    public function test_can_list_units(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        \Modules\MasterData\Models\Unit::factory()->create([
            'name' => 'Unit Test 1',
            'code' => 'U001',
        ]);

        Livewire::test(ListUnits::class)
            ->assertSee('Unit Test 1');
    }
}
