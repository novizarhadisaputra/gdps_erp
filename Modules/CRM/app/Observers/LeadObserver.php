<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\LeadStatus;
use Modules\CRM\Models\Lead;

class LeadObserver
{
    /**
     * Handle the Lead "creating" event.
     */
    public function creating(Lead $lead): void
    {
        if (empty($lead->status)) {
            $lead->status = LeadStatus::Lead;
        }
    }
}
