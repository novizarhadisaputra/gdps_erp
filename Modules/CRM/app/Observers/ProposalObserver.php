<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Enums\ProposalStatus;
use Modules\CRM\Models\Proposal;
use Modules\Finance\Enums\ProfitabilityAnalysisStatus;

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
    }

    /**
     * Handle the Proposal "created" event.
     */
    public function created(Proposal $proposal): void
    {
        if ($proposal->lead && $proposal->lead->status->weight() < LeadStatus::Negotiation->weight()) {
            $proposal->lead()->update(['status' => LeadStatus::Negotiation]);
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

        // 2. If Proposal is moved to Draft (Revised), reset linked PA status and track revision
        if ($proposal->wasChanged('status') && $proposal->status === ProposalStatus::Draft) {
            // Track revision info on Proposal
            $proposal->updateQuietly([
                'revision_number' => $proposal->revision_number + 1,
                'previous_code' => $proposal->proposal_number,
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
    }
}
