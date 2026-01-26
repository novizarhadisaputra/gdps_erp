<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\WorkSchemes\Pages\ListWorkSchemes;
use Modules\MasterData\Models\WorkScheme;
use Tests\TestCase;

class WorkSchemeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_work_schemes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $workScheme = WorkScheme::factory()->create([
            'name' => 'Test Work Scheme',
        ]);

        Livewire::test(ListWorkSchemes::class)
            ->assertCanSeeTableRecords([$workScheme])
            ->assertSee('Test Work Scheme');
    }
}
