<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\AccrueInvoiceMapping;
use Modules\Finance\Models\Invoice;

class AccrualReversalService
{
    public function __construct(
        protected JournalService $journalService
    ) {}

    /**
     * Perform reversal logic for an invoice.
     * This marks related accruals as reversed and ensures correct GL alignment.
     */
    public function reverseAccrualsForInvoice(Invoice $invoice): void
    {
        // 1. Generate the Reversal Journal Entry
        // JournalService now handles mapping update and reversal journal creation
        $this->journalService->generateReversalFromInvoice($invoice);

        // 2. Update status and actual amounts for linked accrual items
        $mappings = AccrueInvoiceMapping::where('invoice_id', $invoice->id)
            ->with('accrueRevenueItem')
            ->get();

        foreach ($mappings as $mapping) {
            $item = $mapping->accrueRevenueItem;

            // Calculate total actual amount from all active mappings for this item
            $totalActual = AccrueInvoiceMapping::where('accrue_revenue_item_id', $item->id)
                ->whereIn('status', [\Modules\Finance\Enums\AccrueInvoiceMappingStatus::Active, \Modules\Finance\Enums\AccrueInvoiceMappingStatus::Reversed])
                ->sum('allocated_amount');

            $item->update([
                'amount_actual' => $totalActual,
                'is_reversed' => $item->amount_estimated <= $totalActual, // Fully reversed if actual >= estimated
            ]);
        }
    }
}
