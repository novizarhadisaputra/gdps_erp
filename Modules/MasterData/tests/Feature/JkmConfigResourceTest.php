<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JkmConfigs\Pages\ListJkmConfigs;
use Modules\MasterData\Models\JkmConfig;
use Tests\TestCase;

class JkmConfigResourceTest extends TestCase
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

        $jkm = JkmConfig::create([
            'name' => 'Test Jkm Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.3,
            'is_active' => true,
        ]);

        Livewire::test(ListJkmConfigs::class)
            ->assertCanSeeTableRecords([$jkm])
            ->assertSee('Test Jkm Config');
    }
}
