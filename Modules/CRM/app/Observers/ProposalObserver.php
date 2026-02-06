<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\Proposal;

class ProposalObserver
{
    /**
     * Handle the Proposal "saved" event.
     */
    public function saved(Proposal $proposal): void
    {
        if ($proposal->lead && $proposal->lead->salesPlan) {
            $proposal->lead->salesPlan->update([
                'proposal_number' => $proposal->proposal_number,
            ]);

            // Sync to monthly as well
            $proposal->lead->salesPlan->monthlyBreakdowns()->update([
                'proposal_number' => $proposal->proposal_number,
            ]);
        }
    }
}
