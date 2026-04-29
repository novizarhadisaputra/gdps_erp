<?php

namespace Modules\Finance\Observers;

use Modules\Finance\Models\AccrueRevenueItem;

class AccrueRevenueItemObserver
{
    /**
     * Handle the AccrueRevenueItem "saved" event.
     */
    public function saved(AccrueRevenueItem $item): void
    {
        $this->triggerParentSync($item);
    }

    /**
     * Handle the AccrueRevenueItem "deleted" event.
     */
    public function deleted(AccrueRevenueItem $item): void
    {
        $this->triggerParentSync($item);
    }

    /**
     * Trigger synchronization on the parent AccrueRevenue.
     */
    protected function triggerParentSync(AccrueRevenueItem $item): void
    {
        $parent = $item->accrueRevenue;

        if ($parent) {
            // We call save() on the parent to trigger its observer.
            // touch() is also an option if we only want to update timestamps.
            $parent->touch();
        }
    }
}
