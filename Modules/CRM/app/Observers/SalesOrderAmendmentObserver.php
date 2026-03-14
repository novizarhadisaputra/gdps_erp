<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\SalesOrderAmendment;

class SalesOrderAmendmentObserver
{
    /**
     * Handle the SalesOrderAmendment "creating" event.
     */
    public function creating(SalesOrderAmendment $amendment): void
    {
        if (filled($amendment->amendment_number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $salesOrder = $amendment->salesOrder;

        if (! $salesOrder) {
            return;
        }

        // Get the latest sequence for this specific Sales Order
        $latest = SalesOrderAmendment::query()
            ->where('sales_order_id', $salesOrder->id)
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $amendment->year = (int) $year;
        $amendment->sequence_number = $sequence;

        // Base SO number usually looks like GDPS/UB/SO-001/25
        // If we want GDPS/UB/SO-001/AMAND/01/25, we need to split and rejoin or just format
        // Let's extract the "GDPS/UB/SO-NNN" part if possible, or just build it.
        // The user example matches: [SO_PART]/AMAND/[SEQ]/[YY]

        $soNumberParts = explode('/', $salesOrder->so_number);
        // Assuming format is GDPS/UB/SO-001/25
        // Parts: [GDPS, UB, SO-001, 25]

        if (count($soNumberParts) >= 3) {
            $basePart = implode('/', array_slice($soNumberParts, 0, 3)); // GDPS/UB/SO-001
            $amendment->amendment_number = sprintf('%s/AMAND/%02d/%s', $basePart, $sequence, $shortYear);
        } else {
            // Fallback
            $amendment->amendment_number = sprintf('%s/AMAND/%02d/%s', $salesOrder->so_number, $sequence, $shortYear);
        }
    }
}
