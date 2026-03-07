<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\HealthConfigs\Pages\ListHealthConfigs;
use Modules\MasterData\Models\HealthConfig;
use Tests\TestCase;

class HealthConfigResourceTest extends TestCase
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

        $health = HealthConfig::create([
            'name' => 'Test Health Config',
            'employee_type' => 'ppu',
            'employer_rate' => 4.0,
            'employee_rate' => 1.0,
            'is_active' => true,
        ]);

        Livewire::test(ListHealthConfigs::class)
            ->assertCanSeeTableRecords([$health])
            ->assertSee('Test Health Config');
    }
}
