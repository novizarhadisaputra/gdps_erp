<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\SalesOrderAmendmentStatus;
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
    /**
     * Handle the SalesOrderAmendment "updated" event.
     */
    public function updated(SalesOrderAmendment $amendment): void
    {
        // When an amendment is approved, sync its "After" state back to the Sales Order
        if ($amendment->isDirty('status') && $amendment->status === SalesOrderAmendmentStatus::Approved) {
            $so = $amendment->salesOrder;
            if ($so) {
                $after = $amendment->after_snapshot;
                
                // 1. Calculate new service total amount (monthly)
                $totalServiceMonth = collect($after['items'] ?? [])->sum('total_price');
                
                // 2. Add Management Fee and Tax (following original SO percentages)
                $mgtFeeVal = $totalServiceMonth * ($so->management_fee_percentage / 100);
                $taxVal = ($totalServiceMonth + $mgtFeeVal) * ($so->tax_percentage / 100);
                $newGrandTotal = $totalServiceMonth + $mgtFeeVal + $taxVal;

                $so->update([
                    'content_config' => array_merge($so->content_config ?? [], [
                        'items' => $after['items'] ?? [],
                        'manpower_details' => $after['manpower_details'] ?? [],
                        'pa_revision_number' => $after['pa_revision_number'] ?? ($so->content_config['pa_revision_number'] ?? 0),
                    ]),
                    'amount' => $newGrandTotal,
                    'manpower_initial_qty' => collect($after['manpower_details'] ?? [])->sum('quantity'),
                ]);
            }
        }
    }
}
