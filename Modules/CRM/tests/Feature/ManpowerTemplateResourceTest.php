<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
use Modules\MasterData\Models\PtkpConfig;
use Modules\MasterData\Models\WorkScheme;
use Tests\TestCase;

class ManpowerTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
    }

    public function test_can_create_record(): void
    {
        $projectArea = ProjectArea::factory()->create();
        $jobPosition = JobPosition::factory()->create();
        $lead = Lead::factory()->create();
        PtkpConfig::create([
            'code' => 'TK/0',
            'name' => 'TK/0',
            'annual_amount' => 54000000,
            'tax_category' => 'A',
            'is_active' => true,
        ]);

        $this->actingAs(User::factory()->create());

        $component = Livewire::test(Pages\CreateManpowerTemplate::class, [
            'parentRecord' => $lead,
        ]);

        $uuid = array_key_first($component->get('data.items') ?? []);

        if (! $uuid) {
            $uuid = (string) Str::uuid();
        }

        $component->fillForm([
            'name' => 'New Manpower Template',
            'project_area_id' => $projectArea->id,
            'work_scheme_id' => WorkScheme::factory()->create()->id,
            'is_active' => true,
            'items' => [
                $uuid => [
                    'job_position_id' => $jobPosition->id,
                    'quantity' => 1,
                    'basic_salary' => 5000000,
                    'future_adjustment_rate' => 0,
                    'ptkp_status' => 'TK/0',
                    'risk_level' => 'very_low',
                    'employee_type' => 'ppu',
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
        $lead = Lead::factory()->create();

        $record = ManpowerTemplate::factory()->create([
            'project_area_id' => $projectArea->id,
            'lead_id' => $lead->id,
        ]);

        $this->actingAs(User::factory()->create());

        $component = Livewire::test(Pages\EditManpowerTemplate::class, [
            'record' => $record->id,
            'parentRecord' => $lead,
        ]);

        $component->fillForm([
            'name' => 'Updated Template Name',
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
        ])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('manpower_templates', [
            'id' => $record->id,
            'name' => 'Updated Template Name',
        ]);
    }
}
