<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (filled($purchaseOrder->number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = PurchaseOrder::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $purchaseOrder->year = $year;
        $purchaseOrder->sequence_number = $sequence;
        $purchaseOrder->number = sprintf('GDPS/UB/PO-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the PurchaseOrder "saved" event.
     */
    public function saved(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->lead && $purchaseOrder->lead->salesPlan) {
            $purchaseOrder->lead->salesPlan->updateQuietly([
                'po_number' => $purchaseOrder->number,
            ]);
        }
    }
}
