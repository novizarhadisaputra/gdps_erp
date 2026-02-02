<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Proposal;

class ProposalObserver
{
    /**
     * Handle the Proposal "creating" event.
     */
    public function creating(Proposal $proposal): void
    {
        if ($proposal->lead_id && $proposal->lead) {
            $proposal->customer_id = $proposal->customer_id ?? $proposal->lead->customer_id;
            $proposal->work_scheme_id = $proposal->work_scheme_id ?? $proposal->lead->work_scheme_id;
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
        if ($proposal->lead_id && $proposal->lead) {
            $proposal->lead->update([
                'status' => LeadStatus::Proposal,
            ]);
        }
    }
}
