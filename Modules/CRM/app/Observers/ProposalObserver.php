<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\Proposal;

class ProposalObserver
{
    /**
     * Handle the Proposal "creating" event.
     */
    public function creating(Proposal $proposal): void
    {
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
        if ($proposal->lead) {
            $proposal->lead->update([
                'status' => \Modules\CRM\Enums\LeadStatus::Proposal,
            ]);
        }
    }

    /**
     * Handle the Proposal "updated" event.
     */
    public function updated(Proposal $proposal): void
    {
        if ($proposal->wasChanged('status') && $proposal->status === \Modules\CRM\Enums\ProposalStatus::Approved) {
            if ($proposal->lead) {
                $proposal->lead->update([
                    'status' => \Modules\CRM\Enums\LeadStatus::Negotiation,
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
