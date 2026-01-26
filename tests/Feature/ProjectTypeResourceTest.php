<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\MasterData\Filament\Resources\ProjectTypes\Pages\ListProjectTypes;
use Modules\MasterData\Models\ProjectType;
use Tests\TestCase;

class ProjectTypeResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_list_project_types(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $projectType = ProjectType::factory()->create([
            'name' => 'Test Project Type',
        ]);

        Livewire::test(ListProjectTypes::class)
            ->assertCanSeeTableRecords([$projectType])
            ->assertSee('Test Project Type');
    }
}
