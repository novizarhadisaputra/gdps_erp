<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
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
    public function test_can_list_units_from_api(): void
    {
        $user = User::factory()->create();

        // Mock the external API response
        Http::fake([
            'https://staffing.garudapratama.com/api/v1/units*' => Http::response([
                'data' => [
                    [
                        'ORGANISASI_ID' => '1',
                        'ORGANISASI_CODE' => 'U001',
                        'ORGANISASI_NAMA' => 'Unit Test 1',
                    ],
                ],
                'total' => 1,
                'success' => true,
            ], 200),
        ]);

        $this->actingAs($user);

        Livewire::test(ListUnits::class)
            ->assertSee('Unit Test 1');
    }
}
