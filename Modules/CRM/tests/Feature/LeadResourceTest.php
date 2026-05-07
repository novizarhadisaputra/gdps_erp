<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\EditLead;
use Modules\CRM\Filament\Clusters\CRM\Resources\Leads\Pages\ListLeads;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\MasterData\Models\JobPosition;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class LeadResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Bypass permission check
        \Illuminate\Support\Facades\Gate::before(fn () => true);

        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_render_the_list_page(): void
    {
        $this->actingAs($this->user);
        Lead::factory()->count(3)->create();

        Livewire::test(ListLeads::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_can_auto_fill_title_when_customer_is_selected(): void
    {
        $this->actingAs($this->user);
        $customer = Customer::factory()->create(['name' => 'PT Testing Indonesia']);

        // Create lead with empty title to trigger auto-fill
        $lead = Lead::factory()->create(['title' => '']);

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->set('data.customer_id', $customer->id)
            ->assertFormSet([
                'title' => 'PT Testing Indonesia Lead',
            ]);
    }

    /** @test */
    public function it_shows_project_area_only_after_customer_is_selected(): void
    {
        $this->actingAs($this->user);

        // Use a valid customer but start the Livewire form with customer_id as null
        $lead = Lead::factory()->create();

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->set('data.customer_id', null)
            ->assertFormFieldIsHidden('project_area_id')
            ->set('data.customer_id', Customer::factory()->create()->id)
            ->assertFormFieldIsVisible('project_area_id');
    }

    /** @test */
    public function it_filters_project_areas_by_selected_customer(): void
    {
        $this->actingAs($this->user);

        $customerA = Customer::factory()->create();
        $customerB = Customer::factory()->create();

        $areaA = ProjectArea::factory()->create(['name' => 'Area Customer A']);
        $areaA->customers()->attach($customerA->id);

        $areaB = ProjectArea::factory()->create(['name' => 'Area Customer B']);
        $areaB->customers()->attach($customerB->id);

        $lead = Lead::factory()->create(['customer_id' => $customerA->id]);

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->set('data.customer_id', $customerA->id)
            ->assertFormFieldExists('project_area_id', function (Select $component) use ($areaA, $areaB) {
                $options = $component->getOptions();

                return isset($options[$areaA->id]) && ! isset($options[$areaB->id]);
            });
    }

    /** @test */
    public function it_can_validate_all_required_fields(): void
    {
        $this->actingAs($this->user);
        $lead = Lead::factory()->create();

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->fillForm([
                'customer_id' => null,
                'title' => null,
            ])
            ->call('save')
            ->assertHasFormErrors([
                'customer_id' => 'required',
                'title' => 'required',
            ]);
    }

    /** @test */
    public function it_correctly_parses_and_saves_currency_amounts(): void
    {
        $this->actingAs($this->user);
        $lead = Lead::factory()->create();

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->fillForm([
                'estimated_amount' => '1.500.000',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'estimated_amount' => 1500000,
        ]);
    }

    /** @test */
    public function it_can_persist_multiple_job_positions(): void
    {
        $this->actingAs($this->user);
        $lead = Lead::factory()->create();
        $positions = JobPosition::factory()->count(2)->create(['is_active' => true])->pluck('id')->toArray();

        Livewire::test(EditLead::class, [
            'record' => $lead->getRouteKey(),
        ])
            ->fillForm([
                'job_positions' => $positions,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $savedLead = Lead::find($lead->id);
        $this->assertEquals($positions, $savedLead->job_positions);
    }
}
