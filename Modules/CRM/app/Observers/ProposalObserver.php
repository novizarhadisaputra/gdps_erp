<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Proposal;
use App\Traits\HasAutoNumber;

class ProposalObserver
{
    use HasAutoNumber;
    /**
     * Handle the Proposal "creating" event.
     */
    public function creating(Proposal $proposal): void
    {
        if ($proposal->lead_id && $proposal->lead) {
            $proposal->customer_id = $proposal->customer_id ?? $proposal->lead->customer_id;
            $proposal->work_scheme_id = $proposal->work_scheme_id ?? $proposal->lead->work_scheme_id;
        }
        
        $this->generateAutoNumber('proposal_number', 'PROP');
    }

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
