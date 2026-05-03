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
        if (filled($amendment->number)) {
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

        $soNumberParts = explode('/', $salesOrder->number);
        // SO Format: GDPS/UB/SO-001/25
        // Parts: [0=>GDPS, 1=>UB, 2=>SO-001, 3=>25]

        if (count($soNumberParts) >= 4) {
            $basePart = implode('/', array_slice($soNumberParts, 0, 3)); // GDPS/UB/SO-001
            $soYear = $soNumberParts[3]; // 25
            $amendment->number = sprintf('%s/AMAND/%02d/%s', $basePart, $sequence, $soYear);
        } else {
            // Fallback: If SO number is shorter or different format
            $amendment->number = sprintf('%s/AMAND/%02d/%s', $salesOrder->number, $sequence, $shortYear);
        }
    }

    /**
     * Handle the SalesOrderAmendment "updated" event.
     */
    public function updated(SalesOrderAmendment $amendment): void
    {
        // Auto-approve if signed SOA is uploaded
        if ($amendment->hasMedia('signed_soa') && $amendment->status !== SalesOrderAmendmentStatus::Approved) {
            $amendment->update(['status' => SalesOrderAmendmentStatus::Approved]);

            return; // updateStatus will trigger another updated event
        }

        // When an amendment is approved, sync its "After" state back to the Sales Order
        if ($amendment->isDirty('status') && $amendment->status === SalesOrderAmendmentStatus::Approved) {
            $so = $amendment->salesOrder;
            if ($so) {
                $after = $amendment->after_snapshot;

                $itemsData = collect($after['items'] ?? []);
                $manpowerData = collect($after['manpower_details'] ?? []);

                // Map manpower for Sales Order level tracking
                $allManpower = $manpowerData->map(fn ($mp) => [
                    'job_position_name' => $mp['job_position_name'] ?? $mp['description'] ?? 'Unknown Position',
                    'quantity' => $mp['quantity'] ?? 0,
                    'unit_price' => $mp['unit_price'] ?? 0,
                    'total_price' => $mp['total_price'] ?? 0,
                ])->toArray();

                // 1. Calculate new service total amount (monthly) - Sum of both items and personnel
                $totalServiceMonth = $itemsData->sum('total_price') + $manpowerData->sum('total_price');

                // 2. Add Management Fee and Tax (following original SO percentages)
                $mgtFeeVal = $totalServiceMonth * ($so->management_fee_percentage / 100);
                $taxVal = round(($totalServiceMonth + $mgtFeeVal) * ($so->tax_percentage / 100), 0);
                $newGrandTotal = round($totalServiceMonth + $mgtFeeVal + $taxVal, 0);

                $so->update([
                    'content_config' => array_merge($so->content_config ?? [], [
                        'items' => $after['items'] ?? [],
                        'manpower_details' => $allManpower,
                        'pa_revision_number' => $after['pa_revision_number'] ?? ($so->content_config['pa_revision_number'] ?? 0),
                    ]),
                    'amount' => $newGrandTotal,
                    'manpower_initial_qty' => collect($allManpower)->sum('quantity'),
                ]);
            }
        }
    }
}
