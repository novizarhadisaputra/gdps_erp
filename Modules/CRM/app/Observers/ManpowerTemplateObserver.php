<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\ManpowerTemplate;

class ManpowerTemplateObserver
{
    /**
     * Handle the ManpowerTemplate "creating" event.
     */
    public function creating(ManpowerTemplate $manpowerTemplate): void
    {
        if (empty($manpowerTemplate->code)) {
            $manpowerTemplate->code = 'MPW-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
        }
    }
}
