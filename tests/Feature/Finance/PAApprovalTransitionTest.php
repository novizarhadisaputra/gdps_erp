<?php

namespace Tests\Feature\Finance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\Finance\Models\ProfitabilityAnalysis;
use Tests\TestCase;

class PAApprovalTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_transitions_lead_to_proposal_stage_when_pa_is_approved(): void
    {
        // 1. Setup: Lead in Approach stage
        $lead = Lead::factory()->create();
        $lead->update(['status' => LeadStatus::Approach]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProfitabilityAnalysisStatus::Draft,
        ]);

        // 2. Action: Approve the PA margin
        $pa->update([
            'is_margin_approved' => true,
        ]);

        // 3. Assertions
        $this->assertEquals(LeadStatus::Proposal, $lead->refresh()->status);
    }

    public function test_it_does_not_transition_lead_if_already_further_than_proposal(): void
    {
        // 1. Setup: Lead already in Negotiation stage
        $lead = Lead::factory()->create();
        $lead->update(['status' => LeadStatus::Negotiation]);

        $pa = ProfitabilityAnalysis::factory()->create([
            'lead_id' => $lead->id,
            'status' => ProfitabilityAnalysisStatus::Draft,
        ]);

        // 2. Action: Approve the PA margin
        $pa->update([
            'is_margin_approved' => true,
        ]);

        // 3. Assertions: Should stay in Negotiation
        $this->assertEquals(LeadStatus::Negotiation, $lead->refresh()->status);
    }
}
