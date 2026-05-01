<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\AccrueRevenueItem;
use Modules\Finance\Models\Invoice;

class AccrualReversalService
{
    /**
     * Perform reversal logic for an invoice.
     * This marks related accruals as reversed and ensures correct GL alignment.
     */
    public function reverseAccrualsForInvoice(Invoice $invoice): void
    {
        $items = AccrueRevenueItem::where('invoice_id', $invoice->id)
            ->where('is_reversed', false)
            ->get();

        foreach ($items as $item) {
            $item->update([
                'is_reversed' => true,
                'amount_actual' => $invoice->total_amount, // Align with actual billed amount
            ]);

            // Logic for SAP integration (if any) would be triggered here
            // e.g., $sapService->postReversal($item);
        }
    }
}
