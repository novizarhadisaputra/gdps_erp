<?php

namespace Modules\Finance\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\CRM\Models\GeneralInformation;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Modules\MasterData\Models\MinimumWage;
use Modules\MasterData\Models\ProjectArea;
use Tests\TestCase;

class ProfitabilityAnalysisWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Gate::before(fn () => true);
        $this->user = User::factory()->create();

        // Seed some basic master data
        ProjectArea::create(['name' => 'Jakarta', 'is_active' => true]);
        MinimumWage::create([
            'project_area_id' => ProjectArea::first()->id,
            'year' => date('Y'),
            'amount' => 5000000,
            'province' => 'DKI Jakarta',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function it_updates_analysis_details_and_recalculates_on_manpower_edit(): void
    {
        $this->actingAs($this->user);

        $gi = GeneralInformation::factory()->create([
            'project_area_id' => ProjectArea::first()->id,
        ]);
        $paymentTerm = \Modules\MasterData\Models\PaymentTerm::factory()->create();

        $tax = \Modules\MasterData\Models\Tax::factory()->create([
            'category' => 'purchase',
            'is_active' => true,
        ]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Draft,
            'general_information_id' => $gi->id,
            'project_area_id' => $gi->project_area_id,
            'is_manual_cost' => false,
            'year' => date('Y'),
            'payment_term_id' => $paymentTerm->id,
            'tax_id' => $tax->id,
            'management_fee_rate' => 15.0,
            'direct_cost' => 0,
        ]);

        // Mocking the form data for edit_manpower action
        $formData = [
            'manpowerItems' => [
                [
                    'job_position_id' => \Modules\MasterData\Models\JobPosition::factory()->create()->id,
                    'quantity' => 2,
                    'unit_cost_price' => 6000000,
                    'total_monthly_cost' => 12000000,
                ],
            ],
            'direct_cost' => 12000000,
            'general_information_id' => $gi->id,
            'customer_id' => $pa->customer_id,
            'product_cluster_id' => $pa->product_cluster_id,
            'work_scheme_id' => $pa->work_scheme_id,
            'project_area_id' => $pa->project_area_id,
            'year' => date('Y'),
            'project_type_id' => $pa->project_type_id,
            'tax_id' => $tax->id,
            'payment_term_id' => $paymentTerm->id,
            'asset_ownership' => \Modules\Finance\Enums\AssetOwnership::GdpsOwned->value,
            'management_fee_rate' => 15.0,
            'interest_rate' => 1.5,
            'tax_rate' => 11.0,
        ];

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->callAction('edit_manpower', data: $formData)
            ->assertHasNoFormErrors();

        $pa->refresh();

        $this->assertEquals(12000000, (float) $pa->direct_cost);
        $this->assertArrayHasKey('calculation_context', $pa->analysis_details);
    }

    /** @test */
    public function it_persists_manual_cost_breakdown_correctly(): void
    {
        $this->actingAs($this->user);

        $gi = GeneralInformation::factory()->create();
        $paymentTerm = \Modules\MasterData\Models\PaymentTerm::factory()->create();
        $tax = \Modules\MasterData\Models\Tax::factory()->create([
            'category' => 'purchase',
            'is_active' => true,
        ]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'status' => ProfitabilityAnalysisStatus::Draft,
            'is_manual_cost' => true,
            'general_information_id' => $gi->id,
            'payment_term_id' => $paymentTerm->id,
            'tax_id' => $tax->id,
            'management_fee_rate' => 15.0,
            'direct_cost' => 0,
        ]);

        $category = \Modules\MasterData\Models\DirectCostCategory::create([
            'name' => 'Operational',
            'code' => 'ops',
            'type' => 'direct',
        ]);

        $formData = [
            'analysis_details' => [
                'manual_costs' => [
                    [
                        'direct_cost_category_id' => $category->id,
                        'amount' => 5000000,
                        'description' => 'Test Manual Cost',
                    ],
                ],
            ],
            'direct_cost' => 5000000,
            'general_information_id' => $gi->id,
            'customer_id' => $pa->customer_id,
            'product_cluster_id' => $pa->product_cluster_id,
            'work_scheme_id' => $pa->work_scheme_id,
            'project_area_id' => $pa->project_area_id,
            'year' => date('Y'),
            'project_type_id' => $pa->project_type_id,
            'tax_id' => $tax->id,
            'payment_term_id' => $paymentTerm->id,
            'asset_ownership' => \Modules\Finance\Enums\AssetOwnership::GdpsOwned->value,
            'management_fee_rate' => 15.0,
            'interest_rate' => 1.5,
            'tax_rate' => 11.0,
        ];

        Livewire::test(\Modules\Finance\Filament\Clusters\Finance\Resources\ProfitabilityAnalyses\Pages\SummaryProfitabilityAnalysis::class, [
            'record' => $pa->getRouteKey(),
        ])
            ->callAction('edit_manual', data: $formData)
            ->assertHasNoFormErrors();

        $pa->refresh();

        $this->assertEquals(5000000, (float) $pa->direct_cost);
        $this->assertCount(1, $pa->analysis_details['manual_costs']);
    }
}
