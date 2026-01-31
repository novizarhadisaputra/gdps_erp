<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Contract;

class ContractObserver
{
    /**
     * Handle the Contract "created" event.
     */
    public function created(Contract $contract): void
    {
        // Check if contract is linked to a proposal, and that proposal is linked to a lead
        if ($contract->proposal_id && $contract->proposal && $contract->proposal->lead) {
            $contract->proposal->lead->update([
                'status' => LeadStatus::Won,
            ]);
        }
        // Direct link to lead (if exists, though usually via Proposal)
        // Checking schema, Contract has 'customer_id' and 'proposal_id'.
        // Does it have 'lead_id'? Let's assume via Proposal first.
    }
}
