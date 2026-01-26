<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\ProjectAreas\Pages\ListProjectAreas;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class ProjectAreaResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_project_areas(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $projectArea = ProjectArea::factory()->create([
            'name' => 'Test Project Area',
        ]);

        Livewire::test(ListProjectAreas::class)
            ->assertCanSeeTableRecords([$projectArea])
            ->assertSee('Test Project Area');
    }
}
