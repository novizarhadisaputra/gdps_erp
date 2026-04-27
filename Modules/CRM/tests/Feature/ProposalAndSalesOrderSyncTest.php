<?php

namespace Modules\CRM\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\CostingTemplate;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\ManpowerTemplate;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\SalesOrder;
use Modules\CRM\Models\SalesOrderAmendment;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class ProposalAndSalesOrderSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function it_syncs_pa_revenue_to_proposal_if_proposal_amount_is_zero_and_pa_is_approved()
    {
        $lead = Lead::factory()->create();
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'amount' => 0,
        ]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProfitabilityAnalysisStatus::Draft,
            'revenue_per_month' => 5000000,
        ]);

        // Update PA while Draft - should NOT sync
        $pa->update(['revenue_per_month' => 6000000]);
        $this->assertEquals(0, $proposal->refresh()->amount);

        // Approve PA - should sync
        $pa->update(['status' => ProfitabilityAnalysisStatus::Approved]);
        $this->assertEquals(6000000, $proposal->refresh()->amount);
    }

    /** @test */
    public function it_does_not_sync_pa_revenue_if_proposal_amount_is_already_set()
    {
        $lead = Lead::factory()->create();
        $proposal = Proposal::factory()->create([
            'lead_id' => $lead->id,
            'amount' => 1000000,
        ]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProfitabilityAnalysisStatus::Draft,
            'revenue_per_month' => 5000000,
        ]);

        // Approve PA - should NOT sync
        $pa->update(['status' => ProfitabilityAnalysisStatus::Approved]);
        $this->assertEquals(1000000, $proposal->refresh()->amount);
    }

    /** @test */
    public function it_generates_sales_order_number_correctly()
    {
        $so = SalesOrder::factory()->create([
            'number' => null,
            'order_date' => now(),
        ]);

        $shortYear = date('y');
        $this->assertMatchesRegularExpression("/GDPS\/UB\/SO-\d{3}\/$shortYear/", $so->number);
        $this->assertEquals(1, $so->sequence_number);
    }

    /** @test */
    public function it_generates_sales_order_amendment_number_correctly()
    {
        $so = SalesOrder::factory()->create([
            'number' => 'GDPS/UB/SO-001/25',
        ]);

        $amendment = SalesOrderAmendment::factory()->create([
            'sales_order_id' => $so->id,
            'number' => null,
        ]);

        // The observer derives the year from the SO number suffix ('25'), not from date('y')
        $this->assertEquals('GDPS/UB/SO-001/AMAND/01/25', $amendment->number);
        $this->assertEquals(1, $amendment->sequence_number);
    }

    /** @test */
    public function it_generates_proposal_revision_number_correctly()
    {
        $proposal = Proposal::factory()->create([
            'status' => ProposalStatus::Approved,
        ]);

        // Mock sequence and year
        $proposal->updateQuietly([
            'number' => 'GDPS/UB/PROP-001/25',
            'sequence_number' => 1,
            'revision_number' => 0,
            'created_at' => '2025-01-01 00:00:00',
        ]);

        $proposal->update(['status' => ProposalStatus::Draft]);

        $shortYearNow = date('y');
        $this->assertEquals("GDPS/UB/PROP-001/REV/01/$shortYearNow", $proposal->number);
        $this->assertEquals(1, $proposal->revision_number);
    }

    /** @test */
    public function it_generates_costing_template_number_correctly()
    {
        $template = CostingTemplate::factory()->create();

        $year = date('Y');
        $shortYear = date('y');
        $this->assertEquals(sprintf('GDPS/UB/TE-001/%s', $shortYear), $template->code);
        $this->assertEquals($year, $template->year);
        $this->assertEquals(1, $template->sequence_number);
    }

    /** @test */
    public function it_generates_manpower_template_number_correctly()
    {
        $template = ManpowerTemplate::factory()->create();

        $year = date('Y');
        $shortYear = date('y');
        $this->assertEquals(sprintf('GDPS/UB/MP-001/%s', $shortYear), $template->code);
        $this->assertEquals($year, $template->year);
        $this->assertEquals(1, $template->sequence_number);
    }
}
