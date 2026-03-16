<?php

namespace Modules\CRM\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\ManpowerTemplateResource;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Resources\ManpowerTemplate\Pages;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
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
        $lead = \Modules\CRM\Models\Lead::factory()->create();

        $this->actingAs(\App\Models\User::factory()->create());

        // Mock the route for InteractsWithParentRecord
        $route = new \Illuminate\Routing\Route(['GET', 'HEAD'], 'crm/leads/{lead}/manpower-costing/create', [
            'as' => 'filament.admin.crm.resources.leads.manpower-costing.create',
        ]);
        $route->bind(request());
        $route->setParameter('lead', (string) $lead->id);
        request()->setRouteResolver(fn () => $route);

        $component = Livewire::test(Pages\CreateManpowerTemplate::class, [
            'lead' => $lead->id,
            'parentRecord' => $lead,
        ]);

        $uuid = array_key_first($component->get('data.items') ?? []);

        if (! $uuid) {
            $uuid = (string) \Illuminate\Support\Str::uuid();
        }

        $component->fillForm([
            'name' => 'New Manpower Template',
            'project_area_id' => $projectArea->id,
            'contract_type_id' => \Modules\MasterData\Models\ContractType::factory()->create()->id,
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory()->create()->id,
            'risk_level' => 'very_low',
            'employee_type' => 'ppu',
            'is_active' => true,
            'items' => [
                $uuid => [
                    'job_position_id' => $jobPosition->id,
                    'quantity' => 1,
                    'basic_salary' => 5000000,
                ],
            ],
        ])
            ->call('create')
            ->assertHasNoErrors()
            ->assertRedirect(ManpowerTemplateResource::getUrl('view', [
                'lead' => $lead->id,
                'record' => ManpowerTemplate::latest()->first()->id,
            ]));

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

        $this->actingAs(\App\Models\User::factory()->create());

        // Mock the route for InteractsWithParentRecord
        $route = new \Illuminate\Routing\Route(['GET', 'HEAD'], 'crm/leads/{lead}/manpower-costing/{record}/edit', [
            'as' => 'filament.admin.crm.resources.leads.manpower-costing.edit',
        ]);
        $route->bind(request());
        $route->setParameter('lead', (string) $lead->id);
        $route->setParameter('record', (string) $record->id);
        request()->setRouteResolver(fn () => $route);

        $component = Livewire::test(Pages\EditManpowerTemplate::class, [
            'lead' => $lead->id,
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
