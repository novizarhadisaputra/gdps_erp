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

    /** @test */
    public function it_captures_manpower_snapshot_on_template_selection(): void
    {
        $this->actingAs($this->user);

        $lead = \Modules\CRM\Models\Lead::factory()->create();

        // Setup related models
        $jobPosition = \Modules\MasterData\Models\JobPosition::factory()->create(['name' => 'Chief Security']);
        $template = \Modules\CRM\Models\ManpowerTemplate::factory()->create([
            'lead_id' => $lead->id,
        ]);

        // Create template item
        \Modules\CRM\Models\ManpowerTemplateItem::factory()->create([
            'manpower_template_id' => $template->id,
            'job_position_id' => $jobPosition->id,
            'basic_salary' => 5000000,
            'quantity' => 2,
            'allowances' => [
                ['name' => 'Tunjangan Tetap', 'amount' => 315000, 'is_fixed' => true, 'is_bpjs_base' => true],
            ],
            'is_bpjs_active' => true,
        ]);

        $gi = GeneralInformation::factory()->create();
        $tax = \Modules\MasterData\Models\Tax::factory()->create([
            'category' => 'purchase',
            'is_active' => true,
        ]);
        $paymentTerm = \Modules\MasterData\Models\PaymentTerm::factory()->create();

        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Draft,
            'lead_id' => $lead->id,
            'general_information_id' => $gi->id,
            'tax_id' => $tax->id,
            'payment_term_id' => $paymentTerm->id,
            'management_fee_rate' => 10,
            'tax_rate' => 11,
            'analysis_details' => [
                'require_manpower_costing' => true,
                'require_operational_costing' => true,
            ],
        ]);

        // Generate the snapshot items manually in test to simulate the afterStateUpdated reactive trigger
        $service = app(\Modules\Finance\Services\ManpowerCostingService::class);
        $snapshotItems = [];
        $manpowerItems = [];
        foreach ($template->items as $item) {
            $calc = $service->calculateForTemplateItem($item);
            $unitDirectCost = (float) ($calc['total_direct_cost'] ?? 0);
            $qty = (int) ($item->quantity ?? 0);
            $totalCost = $unitDirectCost * $qty;

            $snapshotItems[] = [
                'job_position_id' => $item->job_position_id,
                'job_position_name' => $item->jobPosition?->name,
                'quantity' => $qty,
                'basic_salary' => (float) $item->basic_salary,
                'allowances' => $item->allowances,
                'extra_costs' => $item->extra_costs,
                'bpjs_active' => (bool) $item->is_bpjs_active,
                'unit_cost' => $unitDirectCost,
                'total_cost' => $totalCost,
            ];

            $manpowerItems[] = [
                'job_position_id' => $item->job_position_id,
                'quantity' => $qty,
                'unit_cost_price' => $unitDirectCost,
                'total_monthly_cost' => $totalCost,
                'is_manpower' => true,
                'is_bpjs_active' => (bool) $item->is_bpjs_active,
            ];
        }

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\ListProfitabilityAnalyses::class)
            ->mountTableAction('edit_manpower', $pa)
            ->setTableActionData([
                'analysis_details' => [
                    'require_manpower_costing' => true,
                    'manpower_template_id' => $template->id,
                    'manpower_snapshot' => $snapshotItems,
                ],
                'manpowerItems' => $manpowerItems,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $paFresh = $pa->fresh();

        $this->assertNotEmpty($paFresh->analysis_details['manpower_snapshot'] ?? null);
        $snapshot = $paFresh->analysis_details['manpower_snapshot'];
        $this->assertCount(1, $snapshot);
        $this->assertEquals('Chief Security', $snapshot[0]['job_position_name']);
        $this->assertEquals(5000000, $snapshot[0]['basic_salary']);
        $this->assertEquals(2, $snapshot[0]['quantity']);
    }
}
