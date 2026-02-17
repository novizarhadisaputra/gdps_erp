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
