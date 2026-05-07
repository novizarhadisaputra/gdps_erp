<?php

namespace Modules\Project\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectInformationResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_project_creates_information_automatically(): void
    {
        $project = \Modules\Project\Models\Project::factory()->create();

        $this->assertDatabaseHas('project_informations', [
            'project_id' => $project->id,
        ]);
    }
}
