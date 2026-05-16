<?php

namespace Modules\Finance\Services;

use Modules\Finance\Enums\AccrueInvoiceMappingStatus;
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
                ->whereIn('status', [AccrueInvoiceMappingStatus::Active, AccrueInvoiceMappingStatus::Reversed])
                ->sum('allocated_amount');

            $item->update([
                'amount_actual' => $totalActual,
                'is_reversed' => $item->amount_estimated <= $totalActual, // Fully reversed if actual >= estimated
            ]);
        }
    }

    /**
     * Restore accrual amounts for a cancelled invoice.
     */
    public function restoreAccrualsForCancelledInvoice(Invoice $invoice): void
    {
        // 1. Cancel the journals
        $this->journalService->cancelJournalEntries($invoice);

        // 2. Update mappings and restore accrual item values
        $mappings = AccrueInvoiceMapping::where('invoice_id', $invoice->id)
            ->where('status', '!=', AccrueInvoiceMappingStatus::Cancelled)
            ->with('accrueRevenueItem')
            ->get();

        foreach ($mappings as $mapping) {
            $mapping->update(['status' => AccrueInvoiceMappingStatus::Cancelled]);

            $item = $mapping->accrueRevenueItem;

            // Recalculate total actual amount from remaining active mappings
            $totalActual = AccrueInvoiceMapping::where('accrue_revenue_item_id', $item->id)
                ->whereIn('status', [AccrueInvoiceMappingStatus::Active, AccrueInvoiceMappingStatus::Reversed])
                ->sum('allocated_amount');

            $item->update([
                'amount_actual' => $totalActual,
                'is_reversed' => $item->amount_estimated > 0 && $totalActual >= $item->amount_estimated,
            ]);

            // Re-open Accrue Revenue if it was closed
            $accrue = $item->accrueRevenue;
            if ($accrue && $accrue->status === \Modules\Finance\Enums\AccrueRevenueStatus::Closed) {
                $accrue->update(['status' => \Modules\Finance\Enums\AccrueRevenueStatus::Open]);
            }
        }
    }
}
