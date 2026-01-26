<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Project\Filament\Resources\Projects\Pages\ListProjects;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_projects(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::factory()->create([
            'name' => 'Test Project',
            'code' => 'PROJ-001',
        ]);

        Livewire::test(ListProjects::class)
            ->assertCanSeeTableRecords([$project])
            ->assertSee('Test Project');
    }
}
