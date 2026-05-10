<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJkkConfigs\Pages\ListBpjsJkkConfigs;
use Modules\MasterData\Models\BpjsJkkConfig;
use Tests\TestCase;

class JkkConfigResourceTest extends TestCase
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

        $jkk = BpjsJkkConfig::create([
            'name' => 'Test Jkk Config',
            'employee_type' => 'ppu',
            'employer_rate' => 0.24,
            'is_active' => true,
        ]);

        Livewire::test(ListBpjsJkkConfigs::class)
            ->assertCanSeeTableRecords([$jkk])
            ->assertSee('Test Jkk Config');
    }
}
