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

                $items = collect($after['items'] ?? []);

                // Flatten and group manpower for Sales Order level tracking
                $allManpower = $items->flatMap(fn ($item) => $item['manpower'] ?? [])
                    ->groupBy('job_position_name')
                    ->map(fn ($group) => [
                        'job_position_name' => $group->first()['job_position_name'],
                        'quantity' => $group->sum('quantity'),
                    ])
                    ->values()
                    ->toArray();

                // 1. Calculate new service total amount (monthly)
                $totalServiceMonth = $items->sum('total_price');

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
