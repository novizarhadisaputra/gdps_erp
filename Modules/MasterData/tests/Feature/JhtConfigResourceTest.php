<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\JhtConfigs\Pages\ListJhtConfigs;
use Modules\MasterData\Models\JhtConfig;
use Tests\TestCase;

class JhtConfigResourceTest extends TestCase
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

        $jht = JhtConfig::create([
            'name' => 'Test Jht Config',
            'employee_type' => 'ppu',
            'employer_rate' => 3.7,
            'employee_rate' => 2.0,
            'is_active' => true,
        ]);

        Livewire::test(ListJhtConfigs::class)
            ->assertCanSeeTableRecords([$jht])
            ->assertSee('Test Jht Config');
    }
}
