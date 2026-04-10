<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\CRM\Models\ProposalRevision;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;
use Modules\MasterData\Services\SignatureService;

class ProposalObserver
{
    /**
     * Handle the Proposal "creating" event.
     */
    public function creating(Proposal $proposal): void
    {
        // For manual uploads or reference proposals, skip automatic numbering
        if (filled($proposal->proposal_number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = Proposal::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $proposal->year = $year;
        $proposal->sequence_number = $sequence;
        $proposal->proposal_number = sprintf('GDPS/UB/PROP-%03d/%s', $sequence, $shortYear);

        // Naming convention: Customer Name + Proposal
        if (! $proposal->title || $proposal->title === 'New Proposal') {
            $customerName = $proposal->lead?->customer?->name ?? 'Unknown Customer';
            $proposal->title = $customerName.' Proposal';
        }
    }

    /**
     * Handle the Proposal "saving" event.
     */
    public function saving(Proposal $proposal): void
    {
        if ($proposal->profitability_analysis_id && $proposal->profitabilityAnalysis) {
            $proposal->amount = $proposal->profitabilityAnalysis->revenue_per_month;
        }
    }

    /**
     * Handle the Proposal "created" event.
     */
    public function created(Proposal $proposal): void
    {
        if ($proposal->lead && $proposal->lead->status->weight() < LeadStatus::Negotiation->weight()) {
            $proposal->lead()->update(['status' => LeadStatus::Negotiation]);
        }

        // Trigger ProjectReview update
        if ($proposal->lead && $proposal->lead->latestProjectReview) {
            $proposal->lead->latestProjectReview->touch();
        }
    }

    /**
     * Handle the Proposal "updated" event.
     */
    public function updated(Proposal $proposal): void
    {
        if ($proposal->wasChanged('status') && $proposal->status === ProposalStatus::Approved) {
            if ($proposal->lead) {
                $proposal->lead->update([
                    'status' => LeadStatus::Negotiation,
                ]);
            }
        }

        if ($proposal->wasChanged('status') && $proposal->status === ProposalStatus::Submitted) {
            app(SignatureService::class)->notifyNextApprovers($proposal);
        }

        // 2. If Proposal is moved to Draft (Revised), reset linked PA status and track revision
        if ($proposal->wasChanged('status') && $proposal->status === ProposalStatus::Draft) {
            // Create Snapshot Revision
            ProposalRevision::create([
                'proposal_id' => $proposal->id,
                'revision_number' => $proposal->getOriginal('revision_number') ?? 0,
                'snapshot' => $proposal->getRawOriginal(), // Get data before we changed it further
                'reason' => request()->input('reason'), // Capture reason if provided via modal
                'user_id' => auth()->id(),
                'year' => date('Y'),
                'sequence_number' => (ProposalRevision::where('proposal_id', $proposal->id)->max('sequence_number') ?? 0) + 1,
            ]);

            // Track revision info on Proposal
            $newRevisionNumber = $proposal->revision_number + 1;
            $shortYear = date('y', strtotime($proposal->created_at));
            $baseNumber = sprintf('GDPS/UB/PROP-%03d', $proposal->sequence_number);
            $newNumber = sprintf('%s/REV/%02d/%s', $baseNumber, $newRevisionNumber, $shortYear);

            $proposal->updateQuietly([
                'revision_number' => $newRevisionNumber,
                'previous_code' => $proposal->proposal_number,
                'proposal_number' => $newNumber,
            ]);

            // Downgrade Lead status to Approach (Revision stage)
            if ($proposal->lead) {
                $proposal->lead->update([
                    'status' => LeadStatus::Approach,
                ]);
            }

            // Clear signatures from Proposal
            $proposal->signatures()->delete();

            // Cascade to Profitability Analysis
            if ($proposal->profitabilityAnalysis) {
                $proposal->profitabilityAnalysis->update([
                    'status' => ProfitabilityAnalysisStatus::Draft,
                    'is_margin_approved' => false,
                ]);
            }
        }

    }

    /**
     * Handle the Proposal "saved" event.
     */
    public function saved(Proposal $proposal): void
    {
        if ($proposal->lead && $proposal->lead->salesPlan) {
            $proposal->lead->salesPlan->updateQuietly([
                'proposal_number' => $proposal->proposal_number,
                'estimated_value' => $proposal->amount,
            ]);

            // Sync to monthly as well
            $proposal->lead->salesPlan->monthlyBreakdowns()->update([
                'proposal_number' => $proposal->proposal_number,
            ]);
        }

        // Option B: Auto-approve if signed proposal is uploaded (client signed scan)
        if ($proposal->status !== ProposalStatus::Approved && $proposal->hasMedia('signed_proposal')) {
            $proposal->updateQuietly(['status' => ProposalStatus::Approved]);
            // Manually trigger the Lead status update that would happen in updated() if we didn't use updateQuietly
            if ($proposal->lead && $proposal->lead->status->weight() < LeadStatus::Negotiation->weight()) {
                $proposal->lead->update(['status' => LeadStatus::Negotiation]);
            }
        }
    }
}
