<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\CostingTemplate;

class CostingTemplateObserver
{
    /**
     * Handle the CostingTemplate "creating" event.
     */
    public function creating(CostingTemplate $costingTemplate): void
    {
        if (empty($costingTemplate->code)) {
            $costingTemplate->code = 'CST-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
        }
    }
}
