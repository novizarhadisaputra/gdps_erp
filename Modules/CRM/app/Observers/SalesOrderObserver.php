<?php

namespace Modules\CRM\Observers;

use Modules\CRM\Models\SalesOrder;

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
     * Handle the SalesOrder "updated" event.
     */
    public function updated(SalesOrder $salesOrder): void
    {
        if ($salesOrder->wasChanged('status') && $salesOrder->status === \Modules\CRM\Enums\SalesOrderStatus::Approved) {
            // Auto-create WorkCompletionReport (BAPP)
            \Modules\Project\Models\WorkCompletionReport::create([
                'project_id' => $salesOrder->project_id,
                'sales_order_id' => $salesOrder->id,
                'customer_id' => $salesOrder->customer_id,
                'report_number' => 'BAPP-'.str_replace('GDPS/UB/', '', $salesOrder->so_number),
                'document_date' => now(),
                'status' => \Modules\Project\Enums\WorkCompletionStatus::Draft,
                'description' => 'Automatic BAPP from Sales Order '.$salesOrder->so_number,
            ]);
        }
    }
}
