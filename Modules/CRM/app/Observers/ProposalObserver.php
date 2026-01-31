<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Proposal;

class ProposalObserver
{
    /**
     * Handle the Proposal "created" event.
     */
    public function created(Proposal $proposal): void
    {
        if ($proposal->lead_id && $proposal->lead) {
            $proposal->lead->update([
                'status' => LeadStatus::Proposal,
            ]);
        }
    }
}
