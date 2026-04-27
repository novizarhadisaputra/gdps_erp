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
        if (filled($salesOrder->number)) {
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
        $salesOrder->number = sprintf('GDPS/UB/SO-%03d/%s', $sequence, $shortYear);
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
        // Automation removed: BAPP generation is now handled manually 
        // via the "Generate BAPP" button in the Sales Order details page.
    }

    /**
     * Handle the SalesOrder "saved" event.
     */
    public function saved(SalesOrder $salesOrder): void
    {
        // Automation: If Signed SO document is uploaded, flip status to Approved
        if ($salesOrder->hasMedia('signed_so') && $salesOrder->status === SalesOrderStatus::Draft) {
            $salesOrder->updateQuietly([
                'status' => SalesOrderStatus::Approved,
            ]);
        }
    }
}
