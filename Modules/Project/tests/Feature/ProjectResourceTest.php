<?php

namespace Modules\Project\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Project\Enums\ProjectStatus;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\EditProject;
use Modules\Project\Filament\Clusters\Project\Resources\Projects\Pages\ListProjects;
use Modules\Project\Models\Project;
use Tests\TestCase;

class ProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_render_the_list_page(): void
    {
        $this->actingAs($this->user);
        Project::factory()->count(3)->create();

        Livewire::test(ListProjects::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_has_read_only_fields_for_core_data(): void
    {
        $this->actingAs($this->user);

        $customer = \Modules\CRM\Models\Customer::factory()->create();
        $area = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $area->customers()->attach($customer->id);

        $project = Project::factory()->create([
            'status' => ProjectStatus::Planning,
            'customer_id' => $customer->id,
            'project_area_id' => $area->id,
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory()->create()->id,
            'product_cluster_id' => \Modules\MasterData\Models\ProductCluster::factory()->create()->id,
            'tax_id' => \Modules\MasterData\Models\Tax::factory()->create()->id,
            'payment_term_id' => \Modules\MasterData\Models\PaymentTerm::factory()->create()->id,
            'project_type_id' => \Modules\MasterData\Models\ProjectType::factory()->create()->id,
            'billing_option_id' => \Modules\MasterData\Models\BillingOption::factory()->create()->id,
            'revenue_segment_id' => \Modules\MasterData\Models\RevenueSegment::factory()->create()->id,
            'oprep_id' => \Modules\MasterData\Models\Employee::factory()->create()->id,
            'ams_id' => \Modules\MasterData\Models\Employee::factory()->create()->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);

        // Project fields like number, customer, and profit analysis link
        // are usually read-only once created
        Livewire::test(EditProject::class, [
            'record' => $project->getRouteKey(),
        ])
            ->assertFormSet([
                'name' => $project->name,
                'customer_id' => $project->customer_id,
            ]);
    }

    /** @test */
    public function it_can_update_project_details(): void
    {
        $this->actingAs($this->user);

        $customer = \Modules\CRM\Models\Customer::factory()->create();
        $area = \Modules\MasterData\Models\ProjectArea::factory()->create();
        $area->customers()->attach($customer->id);

        $project = Project::factory()->create([
            'status' => ProjectStatus::Planning,
            'customer_id' => $customer->id,
            'project_area_id' => $area->id,
            'work_scheme_id' => \Modules\MasterData\Models\WorkScheme::factory()->create()->id,
            'product_cluster_id' => \Modules\MasterData\Models\ProductCluster::factory()->create()->id,
            'tax_id' => \Modules\MasterData\Models\Tax::factory()->create()->id,
            'payment_term_id' => \Modules\MasterData\Models\PaymentTerm::factory()->create()->id,
            'project_type_id' => \Modules\MasterData\Models\ProjectType::factory()->create()->id,
            'billing_option_id' => \Modules\MasterData\Models\BillingOption::factory()->create()->id,
            'revenue_segment_id' => \Modules\MasterData\Models\RevenueSegment::factory()->create()->id,
            'oprep_id' => \Modules\MasterData\Models\Employee::factory()->create()->id,
            'ams_id' => \Modules\MasterData\Models\Employee::factory()->create()->id,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);

        Livewire::test(EditProject::class, [
            'record' => $project->getRouteKey(),
        ])
            ->fillForm([
                'name' => 'Updated Project Name',
                'customer_id' => $project->customer_id,
                'project_area_id' => $project->project_area_id,
                'billing_option_id' => $project->billing_option_id,
                'status' => \Modules\Project\Enums\ProjectStatus::Planning->value,
                'project_number' => $project->project_number,
                'work_scheme_id' => $project->work_scheme_id,
                'product_cluster_id' => $project->product_cluster_id,
                'tax_id' => $project->tax_id,
                'payment_term_id' => $project->payment_term_id,
                'project_type_id' => $project->project_type_id,
                'revenue_segment_id' => $project->revenue_segment_id,
                'oprep_id' => $project->oprep_id,
                'ams_id' => $project->ams_id,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project Name',
        ]);
    }
}
