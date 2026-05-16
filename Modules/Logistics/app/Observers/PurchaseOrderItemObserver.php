<?php

namespace Modules\Logistics\Observers;

use Modules\Logistics\Models\PurchaseOrderItem;

class PurchaseOrderItemObserver
{
    /**
     * Handle the PurchaseOrderItem "saved" event.
     */
    public function saved(PurchaseOrderItem $item): void
    {
        $this->updatePoTotals($item);
    }

    /**
     * Handle the PurchaseOrderItem "deleted" event.
     */
    public function deleted(PurchaseOrderItem $item): void
    {
        $this->updatePoTotals($item);
    }

    /**
     * Update the totals of the parent Purchase Order.
     */
    private function updatePoTotals(PurchaseOrderItem $item): void
    {
        $purchaseOrder = $item->purchaseOrder;

        if ($purchaseOrder) {
            $totalAmount = $purchaseOrder->items()->sum('total_price');

            // For now, let's assume tax is 11% if not specifically calculated per item
            // or we sum up individual item taxes if we add that column later.
            // Currently our migration has tax_amount in header.

            // Let's sum up taxes if we have a way, or just calculate from total.
            // In our migration, PO has tax_amount and grand_total.

            $taxAmount = $totalAmount * 0.11; // Standard 11%
            $grandTotal = $totalAmount + $taxAmount;

            $purchaseOrder->updateQuietly([
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
            ]);
        }
    }
}
