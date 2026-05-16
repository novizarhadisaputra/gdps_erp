<?php

namespace Modules\Logistics\Observers;

use Modules\Logistics\Models\PurchaseRequestItem;

class PurchaseRequestItemObserver
{
    /**
     * Handle the PurchaseRequestItem "saved" event.
     */
    public function saved(PurchaseRequestItem $item): void
    {
        $this->updatePrTotal($item);
    }

    /**
     * Handle the PurchaseRequestItem "deleted" event.
     */
    public function deleted(PurchaseRequestItem $item): void
    {
        $this->updatePrTotal($item);
    }

    /**
     * Update the grand total of the parent Purchase Request.
     */
    private function updatePrTotal(PurchaseRequestItem $item): void
    {
        $purchaseRequest = $item->purchaseRequest;

        if ($purchaseRequest) {
            $total = $purchaseRequest->items()->sum('total_estimated_price');
            $purchaseRequest->updateQuietly([
                'total_amount' => $total,
            ]);
        }
    }
}
