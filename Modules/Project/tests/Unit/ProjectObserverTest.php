<?php

namespace Modules\Project\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\MasterData\Models\Client;
use Modules\MasterData\Models\ProductCluster;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\Tax;
use Modules\MasterData\Models\WorkScheme;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectObserverTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_project_code_automatically(): void
    {
        // Create master data
        $client = Client::factory()->create(['code' => 'GIA']);
        $scheme = WorkScheme::factory()->create(['code' => '01']);
        $cluster = ProductCluster::factory()->create(['code' => 'BCA']);
        $tax = Tax::factory()->create(['code' => 'P1']);
        $area = ProjectArea::factory()->create(['code' => 'CGK']);

        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'work_scheme_id' => $scheme->id,
            'product_cluster_id' => $cluster->id,
            'tax_id' => $tax->id,
            'project_area_id' => $area->id,
            'project_number' => '0001',
        ]);

        // Expected format: 01BCA P1GIACGK0001
        $this->assertEquals('01BCAP1GIACGK0001', $project->code);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_project_information_automatically(): void
    {
        $client = Client::factory()->create();
        $project = Project::factory()->create(['client_id' => $client->id]);

        $this->assertNotNull($project->information);
        $this->assertInstanceOf(\Modules\Project\Models\ProjectInformation::class, $project->information);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_pads_project_number_to_4_digits(): void
    {
        $client = Client::factory()->create(['code' => 'TST']);
        $scheme = WorkScheme::factory()->create(['code' => '01']);
        $cluster = ProductCluster::factory()->create(['code' => 'BCA']);
        $tax = Tax::factory()->create(['code' => 'P1']);
        $area = ProjectArea::factory()->create(['code' => 'JKT']);

        $project = Project::create([
            'name' => 'Test Project',
            'client_id' => $client->id,
            'work_scheme_id' => $scheme->id,
            'product_cluster_id' => $cluster->id,
            'tax_id' => $tax->id,
            'project_area_id' => $area->id,
            'project_number' => '5',
        ]);

        $this->assertStringEndsWith('0005', $project->code);
    }
}
