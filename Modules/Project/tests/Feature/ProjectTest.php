<?php

namespace Modules\Project\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_project()
    {
        $project = Project::factory()->create();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => $project->name,
        ]);

        $this->assertNotNull($project->code);
        $this->assertNotNull($project->client_id);
    }

    #[Test]
    public function it_generates_unique_project_code()
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $this->assertNotEquals($project1->code, $project2->code);
    }

    #[Test]
    public function it_has_correct_relationships()
    {
        $project = Project::factory()->create();

        $this->assertInstanceOf(\Modules\MasterData\Models\Client::class, $project->client);
        $this->assertInstanceOf(\Modules\MasterData\Models\Employee::class, $project->oprep);
        $this->assertInstanceOf(\Modules\MasterData\Models\Employee::class, $project->ams);
    }
}
