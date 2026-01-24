<?php

namespace Tests\Unit;

use Mockery;
use Modules\Project\Models\Project;
use PHPUnit\Framework\TestCase;

class ProjectCodeGenerationTest extends TestCase
{
    public function test_it_generates_project_code_correctly(): void
    {
        $project = new \stdClass;

        $client = new \stdClass;
        $client->code = 'ABB';

        $area = new \stdClass;
        $area->code = 'JBA';

        $scheme = new \stdClass;
        $scheme->code = '01';

        $cluster = new \stdClass;
        $cluster->code = 'BCL';

        $tax = new \stdClass;
        $tax->code = 'P2';

        $project->client = $client;
        $project->projectArea = $area;
        $project->workScheme = $scheme;
        $project->productCluster = $cluster;
        $project->tax = $tax;
        $project->project_number = '01';

        // Since the method expects a Project instance, we need to pass a real one or make it work with stdClass
        // I will update the Model method to be more flexible or use a real model if needed.
        // Actually, let's use a real model but fill it.

        $realProject = new Project;
        $realProject->setRelation('client', $client);
        $realProject->setRelation('projectArea', $area);
        $realProject->setRelation('workScheme', $scheme);
        $realProject->setRelation('productCluster', $cluster);
        $realProject->setRelation('tax', $tax);
        $realProject->project_number = '01';

        $code = Project::generateProjectCode($realProject);

        $this->assertEquals('ABB01JBA01BCLP2', $code);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
