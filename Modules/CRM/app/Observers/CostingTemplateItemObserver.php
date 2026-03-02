<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\CostingTemplateItem;

class CostingTemplateItemObserver
{
    /**
     * Handle the CostingTemplateItem "created" event.
     */
    public function created(CostingTemplateItem $costingTemplateItem): void
    {
        $costingTemplateItem->costingTemplate?->refreshTotals();
    }

    /**
     * Handle the CostingTemplateItem "updated" event.
     */
    public function updated(CostingTemplateItem $costingTemplateItem): void
    {
        $costingTemplateItem->costingTemplate?->refreshTotals();
    }

    /**
     * Handle the CostingTemplateItem "deleted" event.
     */
    public function deleted(CostingTemplateItem $costingTemplateItem): void
    {
        $costingTemplateItem->costingTemplate?->refreshTotals();
    }
}
