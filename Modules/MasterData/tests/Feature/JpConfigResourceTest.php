<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JpConfigs\Pages\ListJpConfigs;
use Modules\MasterData\Models\JpConfig;
use Tests\TestCase;

class JpConfigResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_configs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $jp = JpConfig::create([
            'name' => 'Test Jp Config',
            'employee_type' => 'ppu',
            'employer_rate' => 2.0,
            'employee_rate' => 1.0,
            'is_active' => true,
        ]);

        Livewire::test(ListJpConfigs::class)
            ->assertCanSeeTableRecords([$jp])
            ->assertSee('Test Jp Config');
    }
}
