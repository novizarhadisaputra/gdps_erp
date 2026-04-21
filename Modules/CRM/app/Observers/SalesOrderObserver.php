<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Enums\SalesOrderStatus;
use Modules\CRM\Enums\SalesOrderType;
use Modules\CRM\Models\SalesOrder;
use Modules\Project\Enums\WorkCompletionStatus;
use Modules\Project\Models\WorkCompletionReport;

class SalesOrderObserver
{
    /**
     * Handle the SalesOrder "creating" event.
     */
    public function creating(SalesOrder $salesOrder): void
    {
        if (filled($salesOrder->so_number)) {
            return;
        }

        $year = date('Y');
        $shortYear = date('y');

        $latest = SalesOrder::query()
            ->where('year', $year)
            ->orderBy('sequence_number', 'desc')
            ->first();

        $sequence = $latest ? $latest->sequence_number + 1 : 1;

        $salesOrder->year = (int) $year;
        $salesOrder->sequence_number = $sequence;
        $salesOrder->so_number = sprintf('GDPS/UB/SO-%03d/%s', $sequence, $shortYear);
    }

    /**
     * Handle the SalesOrder "saving" event.
     */
    public function saving(SalesOrder $salesOrder): void
    {
        // Distinguish Internal vs External based on attachments
        // If a signed SO exists, it's external (requires customer signature)
        // This addresses the feedback regarding attachment-based types
        if ($salesOrder->hasMedia('signed_so')) {
            $salesOrder->type = SalesOrderType::External;
        } else {
            // Default to internal if no external signature present
            $salesOrder->type = SalesOrderType::Internal;
        }
    }

    /**
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        if ($salesOrder->wasChanged('status') && $salesOrder->status === SalesOrderStatus::Approved) {
            // Auto-create WorkCompletionReport (BAPP)
            WorkCompletionReport::create([
                'project_id' => $salesOrder->project_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'report_number' => 'BAPP-'.str_replace('GDPS/UB/', '', $salesOrder->so_number),
                'document_date' => now(),
                'status' => WorkCompletionStatus::Draft,
                'description' => 'Automatic BAPP from Sales Order '.$salesOrder->so_number,
            ]);
        }
    }

    /**
     * Handle the SalesOrder "saved" event.
     */
    public function saved(SalesOrder $salesOrder): void
    {
        // Automation: If Signed SO document is uploaded, flip status to Approved
        // This ensures the SA is legally validated before moving to project execution
        if ($salesOrder->hasMedia('signed_so') && $salesOrder->status === SalesOrderStatus::Draft) {
            $salesOrder->updateQuietly([
                'status' => SalesOrderStatus::Approved,
            ]);

            // Note: Since updateQuietly is used, the 'updated' event above won't trigger
            // from this specific call. If we WANT BAPP creation to also trigger,
            // we should use a regular update() or manually trigger it.
            // BAPP should usually be created once SO is Approved.

            $this->updated($salesOrder->fresh());
        }
    }
}
