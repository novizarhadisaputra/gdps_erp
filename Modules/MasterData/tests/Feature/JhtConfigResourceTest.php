<?php

namespace Modules\MasterData\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\BpjsJhtConfigs\Pages\ListBpjsJhtConfigs;
use Modules\MasterData\Models\BpjsJhtConfig;
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

        $jht = BpjsJhtConfig::create([
            'name' => 'Test Jht Config',
            'employee_type' => 'ppu',
            'employer_rate' => 3.7,
            'employee_rate' => 2.0,
            'is_active' => true,
        ]);

        Livewire::test(ListBpjsJhtConfigs::class)
            ->assertCanSeeTableRecords([$jht])
            ->assertSee('Test Jht Config');
    }
}
