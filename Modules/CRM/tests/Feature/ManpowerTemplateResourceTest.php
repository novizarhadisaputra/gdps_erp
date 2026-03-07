<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class ManpowerTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_record(): void
    {
        $projectArea = ProjectArea::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $lead = \Modules\CRM\Models\Lead::factory()->create();

        $component = Livewire::test(Pages\CreateManpowerTemplate::class, [
            'lead' => $lead->id,
        ]);

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
        $lead = \Modules\CRM\Models\Lead::factory()->create();

        $record = ManpowerTemplate::factory()->create([
            'project_area_id' => $projectArea->id,
            'lead_id' => $lead->id,
        ]);

        $component = Livewire::test(Pages\EditManpowerTemplate::class, [
            'lead' => $lead->id,
            'record' => $record->id,
        ]);

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
