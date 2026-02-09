<?php

namespace Modules\MasterData\Tests\Feature;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplateResource;
use Modules\MasterData\Filament\Clusters\MasterData\Resources\ManpowerTemplates\Pages;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;
use Tests\Traits\TestsFilamentResource;

class ManpowerTemplateResourceTest extends TestCase
{
    use RefreshDatabase, TestsFilamentResource;

    protected function getResource(): string
    {
        return ManpowerTemplateResource::class;
    }

    protected function getValidInput(?Model $record = null): array
    {
        $projectArea = ProjectArea::factory()->create([
            'name' => 'Area '.Str::random(10),
            'code' => 'CODE-'.Str::random(5),
        ]);
        $jobPosition = JobPosition::factory()->create([
            'name' => 'Pos '.Str::random(10),
        ]);

        return [
            'name' => 'Manpower Template '.Str::random(5),
            'project_area_id' => $projectArea->id,
            'description' => 'Test Description',
            'is_active' => true,
            'items' => [
                (string) Str::uuid() => [
                    'job_position_id' => $jobPosition->id,
                    'quantity' => 2,
                    'notes' => 'Test Notes',
                ],
            ],
        ];
    }

    public function test_can_create_record(): void
    {
        $projectArea = ProjectArea::factory()->create();
        $jobPosition = JobPosition::factory()->create();

        $component = Livewire::test(Pages\CreateManpowerTemplate::class);

        // Get the UUID of the initial item added by defaultItems(1)
        $uuid = array_key_first($component->get('data.items') ?? []);

        if (! $uuid) {
            $uuid = (string) Str::uuid();
        }

        $component->fillForm([
            'name' => 'New Manpower Template',
            'project_area_id' => $projectArea->id,
            'is_active' => true,
            'items' => [
                $uuid => [
                    'job_position_id' => $jobPosition->id,
                    'quantity' => 1,
                ],
            ],
        ])
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('manpower_templates', ['name' => 'New Manpower Template']);
    }

    public function test_can_edit_record(): void
    {
        $projectArea = ProjectArea::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $record = \Modules\MasterData\Models\ManpowerTemplate::factory()->create([
            'project_area_id' => $projectArea->id,
        ]);

        $component = Livewire::test(Pages\EditManpowerTemplate::class, [
            'record' => $record->getRouteKey(),
        ]);

        // Get the UUID of the first item
        $uuid = array_key_first($component->get('data.items') ?? []);

        if (! $uuid) {
            $uuid = (string) Str::uuid();
        }

        $component->fillForm([
            'name' => 'Updated Template Name',
            'items' => [
                $uuid => [
                    'job_position_id' => $jobPosition->id,
                    'quantity' => 5,
                ],
            ],
        ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('manpower_templates', [
            'id' => $record->id,
            'name' => 'Updated Template Name',
        ]);
    }
}
