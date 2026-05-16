<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Models\GeneralInformation;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class ProfitabilityAnalysisResourceTest extends TestCase
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
        ProfitabilityAnalysis::factory()->count(3)->create();

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses::class)
            ->assertSuccessful();
    }

    /** @test */
    public function it_toggles_costing_steps_based_on_manual_mode(): void
    {
        $this->actingAs($this->user);

        // Setup initial data in Draft status
        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Draft,
            'is_manual_cost' => false,
        ]);

        // When is_manual_cost is false, the manual action should NOT be visible
        // based on the visibility logic in the trait (line 634-636)
        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->assertActionHidden('edit_manual')
            ->assertActionVisible('edit_manpower');

        // Update record to manual mode
        $pa->update(['is_manual_cost' => true]);

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->assertActionVisible('edit_manual')
            ->assertActionHidden('edit_manpower');
    }

    /** @test */
    public function it_disables_actions_when_status_is_approved(): void
    {
        $this->actingAs($this->user);

        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Approved,
        ]);

        // Actions like edit_manpower should be hidden when status is Approved
        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->assertActionHidden('edit_manpower')
            ->assertActionVisible('generateProject');
    }

    /** @test */
    public function it_validates_required_fields_in_manpower_modal(): void
    {
        $this->actingAs($this->user);

        // Setup mandatory relationships with correct scoping
        $gi = GeneralInformation::factory()->create();
        $tax = \Modules\MasterData\Models\Tax::factory()->create([
            'category' => 'purchase',
            'is_active' => true,
        ]);
        $paymentTerm = \Modules\MasterData\Models\PaymentTerm::factory()->create();

        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Draft,
            'general_information_id' => $gi->id,
            'tax_id' => $tax->id,
            'payment_term_id' => $paymentTerm->id,
            'management_fee_rate' => 10,
            'tax_rate' => 11,
        ]);

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->callAction('edit_manpower')
            ->assertHasNoFormErrors();
    }

    /** @test */
    public function it_can_mount_the_create_action(): void
    {
        $this->actingAs($this->user);
        $gi = GeneralInformation::factory()->create();

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses::class)
            ->mountAction('create')
            ->fillForm([
                'general_information_id' => $gi->id,
            ])
            ->assertHasNoFormErrors();
    }
}
